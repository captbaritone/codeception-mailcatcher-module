# Codeception MailCatcher Module

This module will let you test emails that are sent during your Codeception
acceptance tests. It depends upon you having
[MailCatcher](http://mailcatcher.me/) installed on your development server.

It was inspired by the Codeception blog post: [Testing Email in
PHP](http://codeception.com/12-15-2013/testing-emails-in-php). It is currently
very simple. Send a pull request or file an issue if you have ideas for more
features.

## Version Support

Only version 4.x of this package is supported.

| Package version | Support status |
| --- | --- |
| 4.x | Supported |
| 3.x | Unsupported |
| 2.x | Unsupported |
| 1.x | Unsupported |
| 0.x | Unsupported |

## Installation

Requires PHP 8.3 or later and Codeception 5.

1. Add the package to your `composer.json`:

    `composer require --dev captbaritone/mailcatcher-codeception-module`

2. Configure your project to actually send emails through `smtp://127.0.0.1:1025` in the test environment

3. Enable the module in your `acceptance.suite.yml`:
    ```yaml
    modules:
        enabled:
            - MailCatcher
        config:
            MailCatcher:
                url: 'http://127.0.0.1'
                port: '1080'
    ```

## Optional Configuration

If you need to specify some special options (e.g. SSL verification or authentication
headers), you can set all of the allowed [Guzzle request options](https://docs.guzzlephp.org/en/stable/request-options.html):

    class_name: WebGuy
    modules:
        enabled:
            - MailCatcher
        config:
            MailCatcher:
                url: 'http://127.0.0.1'
                port: '1080'
                guzzleRequestOptions:
                    verify: false
                    debug: true
                    version: 1.0

## Example Usage
```php
<?php

$I->wantTo('Get a password reset email');

// Clear old emails from MailCatcher
$I->resetEmails();

// Reset password
$I->amOnPage('forgotPassword.php');
$I->fillField("input[name='email']", 'user@example.com');
$I->click('Submit');
$I->see('Please check your inbox');

$I->seeInLastEmail('Please click this link to reset your password');
```

## Actions

### resetEmails

Clears the emails in MailCatcher's list. This prevents seeing emails sent
during a previous test. You probably want to do this before you trigger any
emails to be sent

Example:

    <?php
    // Clears all emails
    $I->resetEmails();
    ?>

### seeEmailAttachmentCount

Checks expected count of attachments in last email.

Example:

    <?php
    $I->seeEmailAttachmentCount(1);
    ?>

* Param $expectCount

### seeAttachmentInLastEmail

Checks that last email contains an attachment with filename.

Example:

    <?php
    $I->seeAttachmentInLastEmail('image.jpg');
    ?>

* Param $filename

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

### dontSeeInLastEmail

Checks that an email does NOT contain a value. It searches the full raw text of the
email: headers, subject line, and body.

Example:

    <?php
    $I->dontSeeInLastEmail('Hit me with those laser beams');
    ?>

* Param $text

### dontSeeInLastEmailTo

Checks that the last email sent to an address does NOT contain a value. It searches the
full raw text of the email: headers, subject line, and body.

Example:

    <?php
    $I->dontSeeInLastEmailTo('admin@example.com', 'But shoot it in the right direction');
    ?>

* Param $email
* Param $text

### grabAttachmentsFromLastEmail

Grab Attachments From Email
    
Returns array with the format [ [filename1 => bytes1], [filename2 => bytes2], ...]

Example:

    <?php
    $attachments = $I->grabAttachmentsFromLastEmail();
    ?>

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

### grabUrlsFromLastEmail

Extracts an array of urls from the last email.
It searches the full raw body of the email.
The return value is an array of strings.

Example:

    <?php
    $urls = $I->grabUrlsFromLastEmail();
    ?>

### grabUrlForLinkFromLastEmail

Returns the `href` of the first link whose text exactly matches the label in
the last email's HTML body. Matching is case-sensitive. Nested tags and HTML
entities are decoded. Whitespace in the label is preserved.

Fails if the email has no HTML body or no matching link with an `href`.

Example:

```php
$url = $I->grabUrlForLinkFromLastEmail('Second link');
```

For `<a href="http://second-link.com">Second link</a>`, this returns
`http://second-link.com`.

* Param $label

### grabUrlForLinkFromEmail

Returns the `href` of the first link whose text exactly matches the label in
the given email's HTML body. Matching is case-sensitive. Nested tags and HTML
entities are decoded. Whitespace in the label is preserved.

Fails if the email has no HTML body or no matching link with an `href`.

Example:

```php
$email = $I->lastMessageTo('user@example.com');
$url = $I->grabUrlForLinkFromEmail($email, 'Second link');
```

To use the last email:

```php
$url = $I->grabUrlForLinkFromEmail($I->lastMessage(), 'Second link');
```

For `<a href="http://second-link.com">Second link</a>`, this returns
`http://second-link.com`.

Use an `Email` object from `lastMessage()`, `lastMessageTo()`, or
`lastMessageFrom()`, or one you created directly.

* Param $email
* Param $label

### lastMessageFrom

Grab the full email object sent to an address.

Example:

    <?php
    $email = $I->lastMessageFrom('example@example.com');
    $I->assertNotEmpty($email['attachments']);
    ?>

### lastMessage

Grab the full email object from the last email.

Example:

    <?php
    $email = $I->grabLastEmail();
    $I->assertNotEmpty($email['attachments']);
    ?>

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

### seeInLastEmailSender / seeInLastEmailRecipient

Checks that the sender (or one of the recipients) of the last email contains a value.

Example:

    <?php
    $I->seeInLastEmailSender('noreply@example.com');
    $I->seeInLastEmailRecipient('user@example.com');
    ?>

* Param $text

### Nth email actions

Every "last email" action has an "nth email" counterpart that takes the
position as its first argument. Positions start at 1 and count in the order
the emails were received, so `1` is the first email and `2` the second.
For the `...To` variants, only emails sent to the given address are counted.
Like the existing `...To` actions, addresses match by substring, so
`user@example.com` also matches `superuser@example.com`.

| Last email | Nth email |
| --- | --- |
| `seeInLastEmail($text)` | `seeInNthEmail($nth, $text)` |
| `dontSeeInLastEmail($text)` | `dontSeeInNthEmail($nth, $text)` |
| `seeInLastEmailSubject($text)` | `seeInNthEmailSubject($nth, $text)` |
| `dontSeeInLastEmailSubject($text)` | `dontSeeInNthEmailSubject($nth, $text)` |
| `seeInLastEmailSender($text)` | `seeInNthEmailSender($nth, $text)` |
| `seeInLastEmailRecipient($text)` | `seeInNthEmailRecipient($nth, $text)` |
| `seeInLastEmailTo($email, $text)` | `seeInNthEmailTo($nth, $email, $text)` |
| `dontSeeInLastEmailTo($email, $text)` | `dontSeeInNthEmailTo($nth, $email, $text)` |
| `seeInLastEmailSubjectTo($email, $text)` | `seeInNthEmailSubjectTo($nth, $email, $text)` |
| `dontSeeInLastEmailSubjectTo($email, $text)` | `dontSeeInNthEmailSubjectTo($nth, $email, $text)` |
| `grabMatchesFromLastEmail($regex)` | `grabMatchesFromNthEmail($nth, $regex)` |
| `grabFromLastEmail($regex)` | `grabFromNthEmail($nth, $regex)` |
| `grabMatchesFromLastEmailTo($email, $regex)` | `grabMatchesFromNthEmailTo($nth, $email, $regex)` |
| `grabFromLastEmailTo($email, $regex)` | `grabFromNthEmailTo($nth, $email, $regex)` |
| `grabUrlsFromLastEmail()` | `grabUrlsFromNthEmail($nth)` |
| `grabAttachmentsFromLastEmail()` | `grabAttachmentsFromNthEmail($nth)` |
| `seeAttachmentInLastEmail($filename)` | `seeAttachmentInNthEmail($nth, $filename)` |
| `seeEmailAttachmentCount($count)` | `seeNthEmailAttachmentCount($nth, $count)` |
| `lastMessage()` | `nthMessage($nth)` |
| `lastMessageTo($email)` | `nthMessageTo($nth, $email)` |

Example:

    <?php
    // A sign-up sends a welcome email first, then a confirmation email
    $I->seeInNthEmail(1, 'Welcome!');
    $I->seeInNthEmailSubject(2, 'Please confirm your address');
    $I->seeInNthEmailTo(2, 'admin@example.com', 'A new user has signed up');
    ?>

### seeEmailCount

Asserts that a certain number of emails have been sent since the last time
`resetEmails()` was called.

Example:

    <?php
    $match = $I->seeEmailCount(2);
    ?>

* Param $count

# License

Released under the same license as Codeception: [MIT](https://github.com/captbaritone/codeception-mailcatcher-module/blob/master/LICENSE)
