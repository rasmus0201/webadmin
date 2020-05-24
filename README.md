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

```
Cmnd_Alias WEBADMINCMNDS = /etc/init.d/nginx reload, /usr/local/bin/certbot *, /usr/local/bin/composer *
%webadmin ALL=(ALL) NOPASSWD: WEBADMINCMNDS
```
