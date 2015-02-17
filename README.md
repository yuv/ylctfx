# ylctfx

YLCTFX: Yuval Levy's Contact Form
=================================

(C) 2002-2015 Yuval Levy, http://www.photopla.net

Released under the MIT license (see LICENSE file), although some included dependencies are released under other licenses (details below).

Problem:   Spambots dump all sorts of stuff on HTML contact forms.
Solution:  Expose the form to javascript enabled clients only.
Rationale: Contact forms are for humans.
           99% of non-javascript clients are bots.

Requirements:
  * A decent server operating system. Tested on FreeBSD, Debian, Ubuntu.
  * A web server with PHP support.  Tested on Apache and nginx.

Installation:
  * for convenience, the dependencies have already been pulled into the repo
  * just unpack into a website's arborescence and start configuring
  * configure list of recipients

Usage:
  * customize ylajctc.php contacts in sync with js/yl_recipients.js 
  * synchronize $salt on yl_ctfx.php an yl_ajctc.php
  * include yl_ctfx.php within the HTML <HEAD> tag of a PHP-processed page
  * customize further the visual aspect by editing the CSS files

Many thanks to the folling projects on which YLCTFX depends:
(files have been imported into YLCTFX repository for convenience)

* Swiftmailer
    url:          http://swiftmailer.org/
    repo:         https://github.com/swiftmailer/swiftmailer
    download:     https://github.com/swiftmailer/swiftmailer/archive/v5.3.1.tar.gz
    version:      5.3.1
    last updated: 05-Dec-2014
    license:      LGPL
    destination:  rename whole untarred archive to /swift
    clean up:     # save 3.6MB or 75%
                  rm -fr CHANGES composer.json doc LICENSE notes phpunit.xml.dist README tests

* jQuery
    url:          http://jquery.com/
    repo:         https://github.com/jquery/jquery
    download:     https://github.com/jquery/jquery/archive/2.1.3.tar.gz
    version:      2.1.3
    last updated: 18-Dec-2014
    license:      MIT
    destination:  cp jquery-2.1.3/dist/* /js/

* jquery-validate
    url:          http://jqueryvalidation.org/
    repo:         https://github.com/jzaefferer/jquery-validation
    download:     http://jqueryvalidation.org/files/jquery-validation-1.13.1.zip
    version:      1.13.1
    last updated: 14-Oct-2014
    license:      MIT
    notes:        * No dist on github (bad)
                  * zip unpacks on location, not in subfolder (bad)
    destination:  cp -a /dist/* /js/

* jquery form plugin
    url:          http://jquery.malsup.com/form/
    repo:         https://github.com/malsup/form
    download:     https://github.com/malsup/form/archive/3.51.tar.gz
    minified:     http://oss.maxcdn.com/jquery.form/3.50/jquery.form.min.js
    version:      3.50 (did not find 3.51 minified)
    last updated: 02-Sep-2014
    license:      MIT/GPL
    destination:  cp *.js /js

This code has been helpful to me, I hope it is to you too!

Yuv