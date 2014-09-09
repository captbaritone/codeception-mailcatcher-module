# Codeception MailCatcher Module

This module will let you test emails that are sent during your Codeception
acceptance tests. It depends upon you having
[MailCatcher](http://mailcatcher.me/) installed on your development server.
I have it installed as part of my [development virtual
machine](https://github.com/captbaritone/vagrant-lamp).

It was inspired by the Codeception blog post: [Testing Email in
PHP](http://codeception.com/12-15-2013/testing-emails-in-php). It is currently
very simple. Send a pull request or file an issue if you have ideas for more
features.

## Installation

Add the package into your composer.json:

    {
        "require-dev": {
            "codeception/codeception": "*",
            "captbaritone/mailcatcher-codeception-module": "dev-master"
        }
    }

Tell Composer to download the package:

    php composer.phar update

Then enable it in your `acceptance.suite.yml` configuration and set the url and
port of your site's MailCatcher installation:

    class_name: WebGuy
    modules:
        enabled:
            - MailCatcher
        config:
            MailCatcher:
                url: 'http://project.dev'
                port: '1080'

## Example Usage

    <?php

    $I = new WebGuy\AdminSteps($scenario);
    $I->wantTo('Get a password reset email');

    // Cleared old emails from MailCatcher
    $I->resetEmails();

    // Reset 
    $I->amOnPage('forgotPassword.php');
    $I->fillField("input[name='email']", 'user@example.com');
    $I->click("Submit");
    $I->see("Please check your email");

    $I->seeInLastEmail("Please click this link to reset your password");

## Actions

### resetEmails

Clears the emails in MailCatcher's list. This is prevents seeing emails sent
during a previous test. You probably want to do this before you trigger any
emails to be sent

Example:

    <?php
    // Clears all emails
    $I->resetEmails();
    ?>

### seeInLastEmail

Checks that an email contains a value. It searches the full raw text of the
email: headers, subject line, and body.

Example:

    <?php
    $I->seeInLastEmail('Thanks for signing up!');
    ?>

* Param $text

### seeInLastEmailTo

Checks that the last email sent to an address contains a value. It searches the
full raw text of the email: headers, subject line, and body.

This is useful if, for example a page triggers both an email to the new user,
and to the administrator.

Example:

    <?php
    $I->seeInLastEmailTo('user@example.com', 'Thanks for signing up!');
    $I->seeInLastEmailTo('admin@example.com', 'A new user has signed up!');
    ?>

* Param $email
* Param $text

### dontSeeInLastEmail, dontSeeInLastEmailTo

Checks that the last email/last email sent to an address does NOT contain a value. 

### grabMatchesFromLastEmail

Extracts an array of matches and sub-matches from the last email based on
a regular expression. It searches the full raw text of the email: headers,
subject line, and body. The return value is an array like that returned by
`preg_match()`.

Example:

    <?php
    $matches = $I->grabMatchesFromLastEmail('@<strong>(.*)</strong>@');
    ?>

* Param $regex

### grabFromLastEmail

Extracts a string from the last email based on a regular expression.
It searches the full raw text of the email: headers, subject line, and body.

Example:

    <?php
    $match = $I->grabFromLastEmail('@<strong>(.*)</strong>@');
    ?>

* Param $regex

### grabMatchesFromLastEmailTo

Extracts an array of matches and sub-matches from the last email to a given
address based on a regular expression. It searches the full raw text of the
email: headers, subject line, and body. The return value is an array like that
returned by `preg_match()`.

Example:

    <?php
    $matchs = $I->grabMatchesFromLastEmailTo('user@example.com', '@<strong>(.*)</strong>@');
    ?>

* Param $email
* Param $regex

### grabFromLastEmailTo

Extracts a string from the last email to a given address based on a regular
expression.  It searches the full raw text of the email: headers, subject
line, and body.

Example:

    <?php
    $match = $I->grabFromLastEmailTo('user@example.com', '@<strong>(.*)</strong>@');
    ?>

* Param $email
* Param $regex

# License

Released under the same liceces as Codeception: MIT
