INTRODUCTION
============

This file describes the basic steps to install Emexis-Webmail on your
web server.

REQUERIMENTS
============

* WEB server Apache/IIS
* PHP 5 or greater, with driver for PostgreSQL
* PostgreSQL 8 or greater (We recommend utlização PostgreSQL for now is the only fully tested)	
* The framework PEAR with package DB (database abstraction),  Net_socket, Net_SMTP and Mail mime installed

INSTALATION AND BASICS SETTINGS
===============================

* Decompress and put this folder somewhere inside your document root
* Acess the folder config/, within it there are some specific configuration files
	config_imap.php
		 $imapServerAddress = '127.0.0.1';
		 $imapPort = 143;
	config_smtp.php
		$smtpServerAddress = '127.0.0.1';
		$smtpPort = 25;
	config_database.php
		$dsn_pear = 'pgsql://user:password@host:port/database';
		Example: 'pgsql://postgres:postgres@localhost:5432/emexiswebmail';
    config_general.php
		$attachment_dir = '/var/www/attach'; #Is the temporary folder of attachments, make sure it is writable and if there
	config_server.php
		$domain = 'example'
        $squirrelmail_default_language = 'pt_BR';

DATABASE
========

    You must perform these actions as a user postgres or a super user database
	Then use the file inside the folder schema postgres.sql
	Example:

	$ createuser emexis
	$ createdb -O emexis emexiswebmail;
	$ psql emexiswebmail;

	emexis =# ALTER USER emexis WITH PASSWORD 'the_new_password';
	emexis => \i schema/postgres.sql


These are the basic settings for the operation.
