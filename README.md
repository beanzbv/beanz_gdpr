# GDPR

This is a Concrete5 package to provide a cookie disclosure for your website. The visitor can select and deselect
different kind of cookie types that are completely manageable in the dashboard.

Only after accepting the cookies, the different kind of scripts will be loaded. This can include scripts like Google
Analytics tracking codes.

## Requirements

- PHP 7
- Concrete5 8.2.1

## Usage

Upon installation, the package will provide:
- A "Privacy Policy" page for every language.
- A dashboard page to manage your cookie types and the general settings.
- 2 preset Cookie Types: "Analytics" & "Marketing"

### Cookie Types

In your Concrete5 dashboard, you will find a new page underneath "System & Settings" - "SEO & Statistics" called "GDPR".
Here, you see a list of all the cookie types available on your website. You can add, edit, archive and activate the
cookie types, so you only show the cookie types that are needed for your website.

A cookie type has a title, handle and description. For each cookie type, there is a textarea where you can add all
the scripts that need to be loaded when the visitor accepted that cookie type. When you enter a script, 
all html tags present in that script will be removed.

Upon accepting the cookie disclosure, it will remember the settings for one day. The next day, the visitor has to 
accept the cookies again.

An example of a cookie type:

- Name: "Analytics"
- Handle: "analytics"
- Description: "Cookies related to site visits, browser types, etc..."
- Scripts:

```
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-XXXXX-Y', 'auto');
ga('send', 'pageview');
```

When the visitor accepted "Analytics" in the cookie disclosure, the Google Analytics script will be loaded.

If you have a multilingual website, you can enter a translation for the name and description of each cookie type.

### General Settings

You can edit all the text shown in the cookie disclosure. Upon installation, a fallback text will be added.
That text will be visible as placeholder within each field. If you agree with set text, you don't have to enter
anything in the field. 

If you have a multilingual website, that form is visible for each language of your website, 
so you can translate your cookie disclosure correctly.

You also have the opportunity to change the page of the Privacy Policy.

## Special thanks

Ketan Mistry ([@ketanumistry](https://github.com/ketanmistry)):
https://github.com/ketanmistry/ihavecookies

## Author

Ward Haché ([@wardhache](https://github.com/wardhache)) for Beanz BV ([@beanzhq](https://github.com/beanzhq))

## License

This package is available under the MIT license
