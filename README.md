# SSH Background switcher
Switch background color of your terminal emulator when sshing into different server based on their hostnames.

This is a PHP port of https://github.com/fboender/sshbg with Fish shell support.

## Supported Environments

- Bash or Fish shell
- Gnome Terminal


## Installation

Requirements:

- PHP 8 (with pcntl extenstion)
- OpenSSH

First, clone this repository:
````bash
git clone https://github.com/php-backend/ssh-background-switcher.git
cd ssh-background-switcher
````

Then copy `background-switcher.php` to `/usr/local/bin`
````bash
sudo cp background-switcher.php /usr/local/bin/
````

Then open `~/.ssh/config` and add this two line to the host entry that you want to switch background for.
````bash
        PermitLocalCommand yes
        LocalCommand background-switcher.php "%n" production
````

Full host entry should look like this:
````bash
Host {HOSTNAME}
        HostName {SERVER IP)
        User {USERNAME}
        PermitLocalCommand yes
        LocalCommand background-switcher.php "%n" {PROFILE_NAME}
````

Values wrapped with `{}` should be replaced with proper values.

`PROFILE_NAME` can be either `production`, `demo`, or `test`. You can add more profiles or edit existing one by creating a config.json file:
````bash
mkdir ~/.config/ssh-background-switcher
vim ~/.config/ssh-background-switcher/config.json
````

Now insert this content to the json file:
````json
{
	 "default_background_color": "#ffffff",
	 "profiles": {
		"production": "#feffb0",
		"demo": "#f5f7c4",
		"test": "#ffe3e3",
		"default": "#feffb0"
	 },
	 "hostnames": {
		"example.com": "demo",
		"productionsite.com": "production"
	 }
}
````

Now you can change the colors as you wish.

## Usage
Background Color will switch when you ssh into a specified host:
````bash
ssh example.com
````

Background color will reset back to default color when you terminate your ssh connection explicitly.
