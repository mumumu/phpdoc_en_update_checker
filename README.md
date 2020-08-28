## phpdoc English version temporarily update checker.

You can check [phpdoc English version](https://github.com/php/doc-en/) update history and notify it via E-Mail.
This is **temporary** workaround until svn.php.net mail infrastructure come back.

## Requirement

- PHP 7.2+
  * SimpleXML
- composer
- PHPMailer

## Installation

```
$ git clone git@github.com:mumumu/phpdoc_en_update_checker.git
$ cd phpdoc_en_update_checker
$ composer install
```

## Usage

You can change the following setting on `phpdoc_en_update_checker.php`.

```
$config = array(
    'server' => 'localhost:25',
    'To' => 'foo@example.com',
    'From' => 'noreply@example.com',
);
```

Finally, you execute update check command. You will register the last command on the job scheduler like crontab.

```
$ php /path/to/phpdoc_en_update_checker.php
```

## Contributing

- We does not use many of PHPMailer functionality, such as SMTP Auth and so on. Patches welcome.
- We does not confirm whether this works on Windows, Patches welcome, too.
