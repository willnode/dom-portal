# DOM Cloud Portal Manager

This is the actual server code that runs on [portal.domcloud.id](https://portal.domcloud.id). Open sourced because I want to transparent and clear about how your data will be handled and what or how limitations applies for you.

DOM Cloud runs above Azure VM. So it's a hosting business that runs a top of another host service. Why it's exist? Because I get tired of host solutions these days. I want something easy to deploy like Heroku or Docker, but don't want to mess with immutability and no-SQL stuffs that really only benefit when you get like, millions of traffic (which ofcourse, never happen to me).

Any way, in DOM Cloud, you can deploy anything there... WordPress, Laravel, Django, Rails, Express, whatever. It's mutable, you can edit the server file after you deploy them with [templates](domcloud/dom-templates). Edit files using FTP, run commands SSH, use MySQL or Postgres database, or just using Webmin portal. All common hosting tools is there. And best of all, it's start **free**, for real.

But I'm sorry, of course we have to pay the Azure VM. It's definitely not something cheap so I have to limit on resources that you use. For Freedom plan, you get 200 MB storage and 6 GB yearly bandwidth. Not something huge but I think it mostly enough for everyone, I mean, to get people learn more easily in web development. Of course you can increase the limit if you put money on us, but trust me, the pricing will be reasonable, or close to what Azure bills for me. You can see [the pricing details here](https://domcloud.id/en/price).

Two things that may interest you here, is how [we limit you](app/Commands/CronJob.php) and [how we deploy stuff](app/Libraries/TemplateDeployer.php). This portal also need more improvements in localization and UX, honestly.

If you found any security vulnerability in the software or in any DOM Cloud services, kindly [contact me](mailto:willnode@wellosoft.net) first so I can patch the actual server before getting the exploit details visible.
