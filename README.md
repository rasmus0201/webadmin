## Installation

Prerequisites:
- Linux Ubuntu
- nginx
- composer
- certbot

Right now the SSL certifcates is only set up to work with certbot on a `digitalocean` server.

With sudo rights add the `webadmin` group and add www-data to it. You can also add your own user to that group:

`NB: You should not add root to the webadmin group!`

```sh
sudo groupadd webadmin
sudo usermod -a -G webadmin www-data
```

After this you should whitelist commands for the webadmin to use:

```sh
sudo visudo -f /etc/sudoers.d/webadmin
```

A file will open paste this into it:

```sh
Cmnd_Alias WEBADMINCMNDS = /etc/init.d/nginx reload, /usr/local/bin/certbot *, /usr/local/bin/composer *
%webadmin ALL=(ALL) NOPASSWD: WEBADMINCMNDS
```

Next we need to make the binary to execute priviliged webserver operations.

Compile it like so:
```sh
gcc -o ./bin/webserver_manager ./webserver_manager.c
sudo chmod 4511 ./bin/webserver_manager // Make sure the right permissions is set
sudo chown root:root ./bin/webserver_manager
```

It is important that this binary is stored in the `bin` folder.

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

Now you have a mysql admin user with only the needed permissions.


Finally you should create an [api-token from digitalocean.com](https://cloud.digitalocean.com/account/api/tokens/new) with r/w for SSL certifcates to be verified by letsencrypt.

Then do: `cp digitalocean.ini.example digitalocean.ini` and paste your api token in `digitalocean.ini`.
