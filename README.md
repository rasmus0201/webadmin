## Installation

Prerequisites:
- Linux Ubuntu
- nginx
- composer
- certbot

Right now the SSL certifcates is only set up to work with certbot on a `digitalocean` server.

You should whitelist commands for the webadmin system to use (so it can run it with `sudo`):

```sh
sudo visudo -f /etc/sudoers.d/webadmin
```

A file will open paste this into it:

```sh
Cmnd_Alias WEBADMINCMNDS = /etc/init.d/nginx reload, /usr/local/bin/certbot *, /usr/local/bin/composer *
%www-data ALL=(ALL) NOPASSWD: WEBADMINCMNDS
```

Next we need to create a ssh key for www-data, then it can fetch your git repositories! You just need to add the public key to your git hosting service (github, gitlab, etc.). We also need to compile some binaries so `certbot` and `nginx` can be managed by webadmin. To do all of this we created an installer. Run it like so:

```sh
sudo ./install
```

This will also run a composer install and publish the `.env` file.

Next up you should create an administrator for mysql.

Login to mysql as root: `mysql -u root -p` and enter root password.

Then you should create the user: `CREATE USER 'webserver_admin'@'localhost' IDENTIFIED BY 'plz_change_password';`
NB: PLEASE REMEMBER TO CHANGE THE PASSWORD!

Then paste this:
```sql
GRANT ALTER,
ALTER ROUTINE,
CREATE,
CREATE ROUTINE,
CREATE TEMPORARY TABLES,
CREATE USER,
CREATE VIEW,
DELETE,
DROP,
EVENT,
EXECUTE,
GRANT OPTION,
INDEX,
INSERT,
LOCK TABLES,
PROCESS,
REFERENCES,
RELOAD,
SELECT,
SHOW VIEW,
TRIGGER,
UPDATE
ON *.* TO 'webserver_admin'@'localhost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
```

Now you have a mysql admin user with only the needed permissions to manager users and databases.

Finally you should create an [api-token from digitalocean.com](https://cloud.digitalocean.com/account/api/tokens/new) with r/w for SSL certifcates to be verified by letsencrypt.

Then do: `cp digitalocean.ini.example digitalocean.ini` and paste your api token in `digitalocean.ini`.


Of course also remember to do a `php artisan migrate` after you have set up the `.env` file correctly (db connection).
