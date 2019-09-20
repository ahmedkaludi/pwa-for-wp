=== PWA for WP & AMP ===
Contributors: magazine3
Requires at least: 3.0
Tested up to: 5.2
Stable tag: 1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: PWA, Review, Progressive Web Apps, Mobile, Web Manifest, Manifest, Offline Support, Cache, Pagespeed, Service Worker, Web app, pwa


== Description ==
PWA plugin is bringing the power of the Progressive Web Apps to the WP & AMP to take the user experience to the next level!

You can give the APP-like experience to your audience which will get your website to their home screen and works instantly like an APP with offline support.

= Features: =
* <strong>AMP Support</strong>: Full PWA compatibility for AMP.  It works well with AMPforWP & AMP for WordPress by Automattic.
* NEW: Multi site support
* NEW: UTM Tracking
* NEW: OneSignal support with multisite
* NEW: Easily change start URL
* NEW: Caching Strategy for PWA enabled assets
* Cache Expire option added
* <strong>Service Worker </strong>: We have developed a service worker which automatically gets installed in the background and performs the necessary actions such as caching the external objects to reduce the requests and more.
* <strong>APP Banners in Home-screen</strong>: Automatically notifies your visitor to get the APP version of the website which directly gets added with the list of Apps and Home-screen. You can customize the icons for this as well.
* <strong>Web App Manifest</strong>: The Manifest file is necessary to get this functionality rolled out and we have made this automatic and connected with the PWA options.
* <strong>Offline Support</strong>: The PWA version of your site will load blazing fast even when the user is offline. Once the user is online, it will update the content again. It will also count the analytics as well and updates it when the user goes online again.
* <strong>Full Screen & Splash Screen</strong>: When you open the PWA version from the mobile, it will load in full screen without any browser toolbar, which gives it a native app like feel in PWA version. There’s also a welcome splash screen which is totally customizable.
* <strong>Continuous Development</strong>: We will be working hard to continuously develop this PWA solution and release updates constantly so that your forms can continue to work flawlessly.
* Dashboard System Status which helps you understand the setup status of the PWA.
* Application Icon Uploading
* Background color options for Splash screen
* Add your own Application and short name.
* Easily set the start page from options.
* Set Device Orientation easily.
* Tested with Google Lighthouse
* More PWA Features Coming soon.

**We Act Fast on Feedback!**
We are actively developing this plugin and our aim is to make this plugin into the #1 solution for PWA in the world. You can [Request a Feature](https://github.com/ahmedkaludi/pwa-for-wp/issues) or [Report a Bug](https://pwa-for-wp.com/contact-us/).

**Technical Support**
Support is provided in [Forum](https://wordpress.org/support/plugin/pwa-for-wp). You can also [Contact us](https://pwa-for-wp.com/contact-us/), our turn around time on email is around 12 hours. 

**Would you like to contribute?**
You may now contribute to this PWA plugin on Github: [View repository](https://github.com/ahmedkaludi/pwa-for-wp) on Github

== Frequently Asked Questions ==

= How to install and use this PWA plugin? =
After you Active this plugin, and go the PWA Options Dashboard to see the status and setup options accordingly.  

= How do I report bugs and suggest new features? =
You can report the bugs for this PWA plugin [here](https://github.com/ahmedkaludi/pwa-for-wp/issues)

= Will you include features to my request? =

Yes, Absolutely! We would suggest you send your feature request by creating an issue in [Github](https://github.com/ahmedkaludi/pwa-for-wp/issues/new/) . It helps us organize the feedback easily.

= How do I get in touch? =
You can contact us from [here](https://pwa-for-wp.com/contact-us/)

== Changelog ==
= 1.5 (19 September 2019) =
* Improved:  Video play with PWA caching support for streaming contents #136
* Added:  	 Added auto & fullscreen options in orientation, display respectively #94 #96
* Improved:  Serviceworker and other files will display properly even it does not have write permission. #176
* Improved:  PWA working along with onesignal when used with multisite network #169
* Added: 	 Added text for translate in add to home screen banner #174
* Added: 	 The closing button option for Add to Homescreen Banner #156
* Added: 	 Tooltip for PWA option with there tutorials links #170
* Added: 	 Manual push notification default title will be website name.
			 Push notification popup icon should be changable #89

= 1.4 (12 September 2019) =
* Improved: Serve required files from dynamically #149
* Improved:  Add to homescreen icon showing blank in iphone(apple-touch-icon) #113
* Bug Fixed: Landscape orientation is not working in PWA correctly #151
* Bug Fixed: Showing "//" when adding to home screen in Firefox browser(start url) #147
* Added: Upgrade to premium as a menu item in the PWA #174

= 1.3.2 (9 September 2019) =
* Bug Fixed: Fatal error in multisite #167

= 1.3.1 (7 September 2019) =
* Added: Caching strategists documentation #152
* Improved: Option panel improvements #166

= 1.3 (6 September 2019) =
* Added: Option to change start page URL #153
* Feature: Onesignal fully compatible with multisites #164
* Bug Fixed: Service worker installation issue when home url & site url not same #160
* Improved: Multisite service worker installation #163

= 1.2.1 (10 July 2019) =

* Bug Fixed: If CDN compatiblity is enabled, Layout of the website is broken in AMP #140

= 1.2 (04 July 2019) =
* Bug Fixed: Delete the pwaforwp required files on uninstallation #141
* Bug Fixed: Click here to setup for particular file should have setup Instruction link and message. #142
* Bug Fixed: If CDN compatiblity is enabled, Layout of the website is broken #140
* Bug Fixed: AMP pages are broken on normal refresh. #139
* Bug Fixed: If https status is valid and app icon URL is HTTP then redirect it as HTTPS #133
* Bug Fixed: UTM tracking issue in PWA (Campaign parameter has been added) #114
* Bug Fixed: Home url issue #126

= 1.1 (19 June 2019) =
* Added: Manual notification title & app icon in push notification #125
* Added: Option to download required files when file creation permission is not there #108
* Improvements: Increase pre-caching post limit from 50 to unlimited #124
* Bug Fixed: PHP Notice: Undefined index: #119
* Bug Fixed: messaging/permission-blocked and push notification issues in the PWA for a user #104
* Bug Fixed: Enter Urls To Be Cached is showing backend links instead of actual links #102
* Bug Fixed: One Signal issues with Progressive Web Apps (Currently, PWA For WP & AMP is compatible with single site using oneSignal and multisite without oneSignal ( https://wordpress.org/plugins/onesignal-free-web-push-notifications/ )) #81


= 1.0.9 (25 March 2019) =

* Bug Fixed: Security improvement.

= 1.0.8 (01 March 2019) =
* Added     : Option to customize push notification title.
* Bug Fixed : PWA plugin using Firebase’s Development SDK? #78
* Bug Fixed : Un-visited images are not getting pre-cached when pre-cache option is enabled #80

= 1.0.7.1 (04 February 2019) =

* Bug Fixed : On post update – Push notification shows post link but takes to blog home page #77


= 1.0.7 (01 February 2019) =

* Bug Fixed : CDN Compatibility is not working on multisite #71
* Bug Fixed : Cache only for visitors but not logged in users? #68
* Bug Fixed : After updating post/page, the service worker still loads the stale post/page from old cache. #75
* Bug Fixed : Url issue with multisite ( service worker is getting main domain path for all subdomain path ) #72



= 1.0.6 (23 January 2019) =
* Added: Loading icon option has been added inside tools tab.
* Added: Reset option has been added inside tools tab.
* Added: Option added inside Advanced tab to cache the request url from an external domain.
* Improvements: Notice box to ask for review in day interval will not be shown again, if users click no thanks button 
* Bug Fixed : Js console error ( Uncaught ReferenceError: btnAdd is not defined ) #65
* Bug Fixed : PWA jumps to the browser in amp ( Now PWA in amp will redirect its link to native PWA app nor browser) #57
* Bug Fixed : If site and wordpress url is different, PWA can not be installed #63



= 1.0.5 (20 December 2018) =
* New Feature: Compatible with AMP for WordPress ( https://wordpress.org/plugins/amp ) 
* New Feature: Pre Caching - Now the latest number of posts can be pre cached on users end on their first visit. 
* Option to hide and show custom add to home banner on desktop
* Properly prepared for localization to make plugin translatable
* Bug Fixed : This page does not respond with offline #58

= 1.0.4 (27 November 2018) =
* New Feature: Custom responsive add to home banner.
* New Feature: Force update service worker option (This option updates service worker for all users who have installed at once)
* Offline Google Analytics options
* Improvements in CDN compatibility


= 1.0.3.1 (12 November 2018) =
* Custom Trigger for "Add to Homescreen" bug fixed
* Exclude the urls from Cache list bug fixed
* And Minor bugs fixed


= 1.0.3 (26 October 2018) =
* New Feature: Push Notification using firebase
* Added notifcations to review on day interval
* Custom Trigger for "Add to Homescreen"
* Exclude the urls from Cache list
* And Minor bugs fixed

= 1.0.2 (28 September 2018) =
* Apple touch Icons Support #25
* UTM Tracking feature 
* Contact form added so we can help people faster
* Cache Expire time option added 
* Caching Strategy option added
* Improved Add to homescreen option with proper Mini info bar support.
* and Minor bugs fixed

= 1.0.1 (27 August 2018) =
* You can choose to enable PWA compatibility either on AMP or Non-AMP or both.
* CDN Compatibility Added - Service Worker Works perfectly, even with the CDN. https://github.com/ahmedkaludi/pwa-for-wp/issues/9
* Optin added - Service Worker Works perfectly, https://github.com/ahmedkaludi/pwa-for-wp/issues/7
* Manifest of PWA was getting override when there is onesignal Manifest in the bottom of the Code: https://github.com/ahmedkaludi/pwa-for-wp/issues/4
* Minor bugs fixed

= 1.0 (16 August 2018) =
* Version 1.0 Released