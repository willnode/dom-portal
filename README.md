# DOM Cloud Portal

<p><img align="right" src="https://portal.domcloud.id/logo.svg" alt="Logo" width=200px></p>

[![codecov](https://codecov.io/gh/domcloud/dom-portal/branch/master/graph/badge.svg?token=KVP6A2KFXW)](https://codecov.io/gh/domcloud/dom-portal)
[![Build Status](https://travis-ci.com/domcloud/dom-portal.svg?branch=master)](https://travis-ci.com/domcloud/dom-portal)
[![Uptime Robot ratio (30 days)](https://img.shields.io/uptimerobot/ratio/m786188407-1d0bd21c51b4159f15ad894f)](https://status.domcloud.id)
[![License](https://img.shields.io/github/license/domcloud/dom-portal)](LICENSE)

This is the actual server code that runs on [portal.domcloud.id](https://portal.domcloud.id). Open sourced because I want to transparent and clear about how your data will be handled and what or how limitations applies for you.

DOM Cloud runs above Digital Ocean's Droplet. So it's a hosting business that runs a top of another host service. Why it's exist? Because I get tired of host solutions these days. I want something easy to deploy like Heroku or Docker, but don't want to mess with immutability and no-SQL stuffs that really only benefit when you get like, millions of traffic (which of course, never happen to me).

Any way, in DOM Cloud, you can deploy anything there... WordPress, Laravel, Django, Rails, Express, whatever. It's mutable, you can edit the server file after you deploy them with [templates](https://github.com/domcloud/dom-templates). Edit files using FTP, run SSH commands, use MySQL or Postgres database, or just use Webmin portal. All common hosting tools is there. And best of all, it's cost nothing for you to start with.

Two things that may interest you here, is how [we limit you](app/Commands/CronJob.php) and [how we deploy stuff](app/Libraries/TemplateDeployer.php). This portal also need more improvements in localization and UX, honestly.

If you found any security vulnerability in the software or in any DOM Cloud services, kindly [contact me](mailto:willnode@wellosoft.net) first so I can patch the actual server before getting the exploit details visible.
