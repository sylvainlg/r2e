# R2E

> RSS to email

R2E is a simple tool to send rss news to your mailbox.

You can search and add feeds (RSS or atom) to your account and the cronjob will fetch them and send you the news.

## Technical requirement

This website can be installed on most of the web hosters. You need only a PHP 8 runtime, a SQL database and the hability to run cronjobs.

You need to run `bin/console app:fetchfeed` every hour.
