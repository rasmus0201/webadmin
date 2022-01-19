<?php

namespace App\Services;

use App\Helpers\DatabaseSluggifier;
use Exception;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseService
{
    /**
     * @var \Illuminate\Database\Connection
     */
    private $db;

    private $hiddenDatabases = [
        'information_schema',
        'mysql',
        'performance_schema',
    ];

    private $privileges = [
        'all' => 'ALL PRIVILEGES',
        'list' => [
            'ALTER',
            'ALTER ROUTINE',
            'CREATE',
            'CREATE ROUTINE',
            'CREATE TEMPORARY TABLES',
            'CREATE VIEW',
            'DELETE',
            'DROP',
            'EVENT',
            'EXECUTE',
            'INDEX',
            'INSERT',
            'LOCK TABLES',
            'REFERENCES',
            'SELECT',
            'SHOW VIEW',
            'TRIGGER',
            'UPDATE',
        ],
    ];

    public function __construct(DatabaseManager $databaseManager)
    {
        $this->db = $databaseManager->connection('webadmin');
    }

    public function getUserParts($user, $defaultHost = 'localhost')
    {
        $user = str_replace(['\'', '"'], '', $user);

        $username = $user;
        $host = '';

        $parts = explode('@', $user);
        if (count($parts) === 2) {
            $username = $parts[0];
            $host = $parts[1];
        }

        if (empty($host)) {
            $host = $defaultHost;
        }

        if (empty($username)) {
            throw new Exception("Username must not be empty");
        }

        return [
            'username' => $username,
            'host' => $host,
        ];
    }

    public function getSafeUser($user, $defaultHost = 'localhost')
    {
        $user = $this->getUserParts($user, $defaultHost);

        $username = $this->db->getPdo()->quote($user['username']);
        $host = $this->db->getPdo()->quote($user['host']);

        return "$username@$host";
    }

    public function getUserInfo($user, $defaultHost = 'localhost')
    {
        $info = $this->db->table('mysql.user', 'u')
            ->select([
                'u.User as user',
                'u.Host as host',
                'u.max_connections',
                DB::raw("
                    IF(u.Grant_priv = 'Y', 1, 0) AS `grant_privilege`
                "),
                DB::raw("
                    IF(u.Super_priv = 'Y', 1, 0) AS `super_privilege`
                "),
                DB::raw("
                    IF(u.password_expired = 'Y', 1, 0) AS `password_expired`
                "),
                DB::raw("
                    IF(u.account_locked = 'Y', 1, 0) AS `account_locked`
                "),
                'u.max_user_connections',
                'u.password_last_changed',
                'u.password_lifetime',
            ])
            ->where('u.User', $user)
            ->where('u.Host', $defaultHost)
            ->first();

        $info->databases = $this->listDatabasesByUser($user, $defaultHost);

        return collect($info);
    }

    public function getDatabaseInfo($name)
    {
        $tables = $this->db->table('information_schema.tables')
            ->select(['table_name'])
            ->where('table_schema', $name)
            ->pluck('table_name');

        $size = $this->db->table('information_schema.tables')
            ->selectRaw("
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS `size_mb`
            ")
            ->groupBy('table_schema')
            ->where('table_schema', $name)
            ->pluck('size_mb')
            ->first();

        $info = $this->db->table('information_schema.schemata')
            ->select([
                'schema_name as name',
                'default_character_set_name',
                'default_collation_name',
            ])
            ->where('schema_name', $name)
            ->first();

        $info->tables = $tables;
        $info->size_mb = floatval($size);

        return collect($info);
    }

    public function getSafePassword($password)
    {
        $password = $this->db->getPdo()->quote($password);

        return "$password";
    }

    public function listDatabases()
    {
        return $this->db->table('information_schema.schemata')
            ->select('SCHEMA_NAME')
            ->whereNotIn('SCHEMA_NAME', $this->hiddenDatabases)
            ->pluck('SCHEMA_NAME')
            ->toArray();
    }

    public function listUsers()
    {
        return $this->db->table('mysql.user')
            ->select(['user', 'host'])
            ->where('account_locked', 'N')
            ->where('password_expired', 'N')
            ->get()
            ->toArray();
    }

    public function listDatabasesByUser($user, $defaultHost = 'localhost')
    {
        return $this->db->table('mysql.db', 'd')
            ->join('information_schema.schemata as s', 's.SCHEMA_NAME', '=', 'd.Db')
            ->select([
                'd.Db',
            ])
            ->where('d.User', $user)
            ->where('d.Host', $defaultHost)
            ->pluck('Db');
    }

    public function databaseExists($name)
    {
        return $this->db->table('information_schema.schemata')
            ->where('SCHEMA_NAME', $name)
            ->count() === 1;
    }

    public function createDatabase($name)
    {
        if (Str::length($name) > 32) {
            throw new Exception("Database name must be at most 32 characters");
        }

        if (preg_match("/[^A-Za-z0-9_]/", $name)) {
            throw new Exception("Database name contains illegal characters. Allowed are a-z, A-Z, 0-9 and underscore");
        }

        $name = DatabaseSluggifier::database($name);

        if ($this->databaseExists($name)) {
            throw new Exception("Database with name '$name' already exists");
        }

        $ret = $this->db->statement(
            "CREATE DATABASE `$name`"
        );

        if (!$ret) {
            throw new \Exception('Something went wrong on database creation');
        }

        return [
            'database' => $name,
        ];
    }

    public function deleteDatabase($name)
    {
        if (!$this->databaseExists($name)) {
            throw new Exception("Database does not exist");
        }

        $result = $this->db->statement(
            "DROP DATABASE `$name`"
        );

        if (!$result) {
            throw new \Exception('Something went wrong when deleting database');
        }

        $this->flushPrivileges();

        return [
            'database' => $name,
        ];
    }

    public function userExists($user, $defaultHost = 'localhost')
    {
        $userParts = $this->getUserParts($user ,$defaultHost);

        return $this->db->table('mysql.user')
            ->where('User', $userParts['username'])
            ->where('Host', $userParts['host'])
            ->count() === 1;
    }

    public function createUser($username, $host, $password)
    {
        if (Str::length($username) > 32) {
            throw new Exception("Username must be at most 32 characters");
        }

        if (Str::length($password) > 32) {
            throw new Exception("Password must be at most 32 characters");
        }

        $username = DatabaseSluggifier::username($username);

        if ($this->userExists($username)) {
            throw new Exception("Database user '$username' already exists");
        }

        $user = $this->getSafeUser($username, $host);
        $password = $this->getSafePassword($password);

        $userCreated = $this->db->statement(
            Str::replaceArray('?', [$user, $password], "CREATE USER ? IDENTIFIED BY ?")
        );

        if (!$userCreated) {
            throw new \Exception('Something went wrong on database user creation');
        }

        return $this->getUserParts($user);
    }

    public function deleteUser($user, $defaultHost = 'localhost')
    {
        if (!$this->userExists($user, $defaultHost)) {
            throw new Exception("User does not exist");
        }

        $safeUser = $this->getSafeUser($user, $defaultHost);

        $result = $this->db->statement(
            "DROP USER $safeUser"
        );

        if (!$result) {
            throw new \Exception('Something went wrong when deleting user');
        }

        $this->flushPrivileges();

        return $this->getUserParts($safeUser);
    }

    public function getPrivilegesOnDatabase($user, $host, $database)
    {
        // https://blog.devart.com/how-to-get-a-list-of-permissions-of-mysql-users.html

        $result = DB::table('mysql.db', 'md')
            ->selectRaw("
                TRIM(TRAILING ',' FROM CONCAT(
                    IF(md.Select_priv = 'Y', 'SELECT,', ''),
                    IF(md.Insert_priv = 'Y', 'INSERT,', ''),
                    IF(md.Update_priv = 'Y', 'UPDATE,', ''),
                    IF(md.Delete_priv = 'Y', 'DELETE,', ''),
                    IF(md.Create_priv = 'Y', 'CREATE,', ''),
                    IF(md.Drop_priv = 'Y', 'DROP,', ''),
                    IF(md.Grant_priv = 'Y', 'GRANT,', ''),
                    IF(md.References_priv = 'Y', 'REFERENCES,', ''),
                    IF(md.Index_priv = 'Y', 'INDEX,', ''),
                    IF(md.Alter_priv = 'Y', 'ALTER,', ''),
                    IF(md.Create_tmp_table_priv = 'Y', 'CREATE TEMPORARY TABLES,', ''),
                    IF(md.Lock_tables_priv = 'Y', 'LOCK TABLES,', ''),
                    IF(md.Create_view_priv = 'Y', 'CREATE VIEW,', ''),
                    IF(md.Show_view_priv = 'Y', 'SHOW VIEW,', ''),
                    IF(md.Create_routine_priv = 'Y', 'CREATE ROUTINE,', ''),
                    IF(md.Alter_routine_priv = 'Y', 'ALTER ROUTINE,', ''),
                    IF(md.Execute_priv = 'Y', 'EXECUTE,', ''),
                    IF(md.Event_priv = 'Y', 'EVENT,', ''),
                    IF(md.Trigger_priv = 'Y', 'TRIGGER,', '')
                )) AS `privileges`
            ")
            ->where('User', $user)
            ->where('Host', $host)
            ->where('Db', $database)
            ->pluck('privileges')
            ->first();

        return array_filter(explode(',', $result));
    }

    public function setPrivilegesOnDatabase($user, $host, $database, array $privileges)
    {
        if (!$this->userExists($user, $host)) {
            throw new Exception("User does not exist");
        }

        if (!$this->databaseExists($database)) {
            throw new Exception("Database does not exist");
        }

        $result = false;
        $grantedPrivileges = [];
        $safeUser = $this->getSafeUser($user, $host);

        if (in_array('ALL PRIVILEGES', $privileges)) {
            $result = $this->db->statement(
                "GRANT ALL PRIVILEGES ON `$database`.* TO $safeUser"
            );

            $grantedPrivileges[] = 'ALL PRIVILEGES';
        } elseif (count($privileges) === 0) {
            $result = $this->revokePrivilegesOnDatabase($user, $host, $database, ['ALL PRIVILEGES']);
            $grantedPrivileges[] = '(revoked all privileges)';
        } else {
            $grantedPrivileges = array_intersect($privileges, $this->privileges['list']);

            if (count($grantedPrivileges) === 0) {
                throw new \Exception('No valid privileges found');
            }

            $strPrivileges = implode(', ', $grantedPrivileges);
            $result = $this->db->statement(
                "GRANT $strPrivileges ON `$database`.* TO $safeUser"
            );
        }

        if (!$result) {
            throw new \Exception('Something went wrong when setting privileges on database');
        }

        $this->flushPrivileges();

        return $grantedPrivileges;
    }

    public function revokePrivilegesOnDatabase($user, $host, $database, array $privileges)
    {
        if (!$this->userExists($user, $host)) {
            throw new Exception("User does not exist");
        }

        if (!$this->databaseExists($database)) {
            throw new Exception("Database does not exist");
        }

        if (count($privileges) === 0) {
            return;
        }

        $result = false;
        $revokedPrivileges = [];
        $safeUser = $this->getSafeUser($user, $host);

        if (in_array('ALL PRIVILEGES', $privileges)) {
            $result = $this->db->statement(
                "REVOKE ALL PRIVILEGES ON `$database`.* FROM $safeUser"
            );
            $revokedPrivileges = ['ALL PRIVILEGES'];
        } else {
            $revokedPrivileges = array_intersect($privileges, $this->privileges['list']);

            if (count($revokedPrivileges) === 0) {
                throw new \Exception('No valid privileges found');
            }

            $strPrivileges = implode(', ', $revokedPrivileges);
            $result = $this->db->statement(
                "REVOKE $strPrivileges ON `$database`.* FROM $safeUser"
            );
        }

        if (!$result) {
            throw new \Exception('Something went wrong when revoking privileges on database');
        }

        $this->flushPrivileges();

        return $revokedPrivileges;
    }

    public function flushPrivileges()
    {
        if (!$this->db->statement("FLUSH PRIVILEGES")) {
            throw new \Exception('Something went wrong when flushing privileges');
        }

        return true;
    }
}
