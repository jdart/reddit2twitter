#Reddit2Twitter

Send reddit posts to a twitter account.

## Requirements

* PHP 5.4+
* [composer](https://getcomposer.org/)
* php5-curl
* php5-imagick
* mysql
* mysql database
* reddit account
* twitter account
* twitter app with write access

## Installation

Create a directory for your project:
`mkdir project`

Create a config file:
```
mkdir -p project/app/cache
mkdir -p project/app/config
mkdir -p project/app/lock
touch project/app/config/config.yml
```

An example config file:
```
parameters:
    lock.file: "lock/pid"
    lock.next_window: "lock/next_window"
    doctrine.connection:
        driver:   pdo_mysql
        host:     localhost
        dbname:   database_name_goes_here
        user:     database_username_goes_here
        password: database_password_goes_here
    reddit.credentials:
        api_type: json
        user:     reddit_username
        passwd:   reddit_password
    twitter.cache_dir: "cache"
    twitter.cache_lifetime: "-1 hour"
    twitter.credentials:
        user_token: twitter_access_token_goes_here # from https://apps.twitter.com/
        user_secret: twitter_access_token_secret_goes_here
        consumer_key: twitter_application_api_key_goes_here
        consumer_secret: twitter_application_api_secret_goes_here
```

Create the below composer.json file in your project directory:
```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/jdart/reddit2twitter"
        }
    ],
    "require": {
        "jdart/reddit2twitter": "dev-master",
        "themattharris/tmhoauth": "dev-master"
    }
}
```

Run composer
`composer install`

Setup the database:
`./vendor/jdart/reddit2twitter/bin/console orm:schema-tool:create`

Test is out:
`./vendor/jdart/reddit2twitter/bin/console r2t:cron`

Create a cron job for the above command:
`*/30 * * * * /path/to/vendor/jdart/reddit2twitter/bin/console r2t:cron`
