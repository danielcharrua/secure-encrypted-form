=== Secure Encrypted Form ===
Contributors: danidub
Tags: contact form, encryption, openpgp, secure form, privacy
Requires at least: 5.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin adds a secure form in your website that uses OpenPGP encryption to secure sensitive communications.

== Description ==

This plugin allows you to insert a *"secure form"* into your website through a simple shortocde. It is usefull when you need to **receive sensitive data of any kind, establishing a *"safe channel"***.
The data is sent encrypted with your PGP public key.

= Usage =

Just fill in some plugin options:

* The destination email (your email)
* Your PGP public key in ASCII armored version

Hint: to see your private key you can enter your computer console and run two commands, one to list and one to export (must have GnuPG):

`
gpg --list-keys
gpg --armor --export username@email
`

Remember your public key needs to be exported in ASCII armored version, this means that will be surrounded with:

`
-----BEGIN PGP PUBLIC KEY BLOCK-----

your-long-key-string-will-be-here

-----END PGP PUBLIC KEY BLOCK-----
`

Once the shortcode is placed into a page or post, it will render a form with the following fields:

* Name
* Email
* Subject
* Message

= How it works =
The *message* field will be encrypted with your **PGP public key** and sent as an attachment in **ASCII** format to the destination email you have configured.

When creating the plugin logic I have made sure that the *message* field **is never sent to the web server**, the data is previously encrypted (on the fly) using *OpenPGP.js* library in the user who is browsing the website.

You will only be able to decrypt the content of the attached file if you have the PGP private key belonging to the public key with which the message was encrypted.

*Remember that the purpose of the plugin is only to display a form on your website and encrypt the information that is sent through the "message" field. This plugin does not take care of decrypting the attached file, this task is left to each user in the way they want.*

= Some usage examples =

* Receive secret messages
* Receiving passwords from clients or friends
* Reception of sensitive information
* Offering a private channel to sources, whistleblowers or people asking for help

= Why private communication matters =

Some messages cannot travel through an ordinary contact form. A source writing to a journalist, an employee reporting wrongdoing inside their own company, a lawyer receiving documents from a client, someone asking for help where asking is itself dangerous: for all of them, whether the channel is private is not a detail, it is the entire point.

A normal contact form sends the message in plain text to the web server, where it is kept in the database, in mail logs and in whatever backups the hosting provider takes. Every one of those copies is a place where it can be read, demanded or leaked. This plugin encrypts the message in the visitor's browser, so what reaches the server is already unreadable to everyone, including you until you decrypt it with your private key, and including your hosting provider.

The Universal Declaration of Human Rights protects both freedom of expression and privacy of correspondence, and the two hold each other up: people only speak freely when they can choose who is listening. Giving your readers a channel that does not betray them is a small, practical way of defending that.

= What this plugin does not do =

Encrypting the message is one piece of a larger picture, and it is only honest to say where the picture ends.

* The name, email and subject fields are **not** encrypted. Only the message is. Anyone who can read your server can see who wrote and what the subject was.
* The plugin does not hide the fact that someone visited your website.
* It cannot protect a message once you have decrypted it on your own computer.

If someone's safety depends on this channel, the way they reach your site and the way you store what you receive matter just as much as the encryption itself.

= Requirements =

**Your site must be served over HTTPS.** The message is encrypted by the visitor's browser, and browsers only allow encryption on secure connections. On a plain HTTP site no message can be sent at all. If your site is not on HTTPS yet, ask your hosting provider for an SSL certificate, they are usually free.

In order to use this plugin you need to have or create a **PGP key pair**. If you don't have your key pair generated you can browse the internet on how to generate it.
There are many ways to generate the key, each have a different impact on security.

= Recommended software =

* [GNU Privacy Guard (Linux, OS X, Windows)](https://gnupg.org)
* [GPG Suite (OS X)](https://gpgtools.org)
* [Gpg4win (Windows)](https://www.gpg4win.org/)

= Support =

When you cannot find the answer to your question on the FAQ section, check the [support forum](https://wordpress.org/support/plugin/secure-encrypted-form/) on WordPress.org. If you cannot locate any topics that solve to your particular issue, post a new topic for it.
Remember this support is offered for free and can take some hours/days to answer and solve your issues.

= Spam protection (optional) =

The plugin can add a [Cloudflare Turnstile](https://www.cloudflare.com/products/turnstile/) challenge to the form. It is **disabled by default** and does nothing until you enable it and enter your site key and secret key in the plugin settings.

The message is always encrypted in your visitor's browser before anything is sent, with or without Turnstile.

If Cloudflare cannot be reached, submissions are allowed through and the problem is written to the diagnostic log, so an outage never costs you a legitimate message.

= Privacy notices =

With the default configuration, this plugin, in itself, does not:

* Track users by stealth
* Write any user personal data to the database
* Send any data to external servers
* Use cookies

= External services =

This plugin does not connect to any external service unless you enable Cloudflare Turnstile in its settings.

When you do enable it, the plugin relies on Cloudflare Turnstile to tell human visitors apart from bots:

* The form loads the Turnstile widget script from `https://challenges.cloudflare.com/turnstile/v0/api.js`. Loading it makes the visitor's browser contact Cloudflare, which collects the data described in their documentation to run the challenge.
* When the form is submitted, your server sends the token produced by the widget, together with your secret key, to `https://challenges.cloudflare.com/turnstile/v0/siteverify` to check whether the challenge was passed.
* The plugin never sends the message, the form fields or the visitor's IP address to Cloudflare.

Cloudflare's [terms of service](https://www.cloudflare.com/website-terms/) and [privacy policy](https://www.cloudflare.com/privacypolicy/) apply to that service.

= Translations =

Actually the plugin ships in English and is translated to Spanish.
You can contribute and [translate this plugin to your own language](https://translate.wordpress.org/projects/wp-plugins/secure-encrypted-form/).

== Installation ==

1. Upload the entire `secure-encrypted-form` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the **Plugins** screen (**Plugins > Installed Plugins**).

You will find **Secure Encrypted Form** menu in your WordPress admin screen. Once configured, insert the form in any page or post using the shortcode `[secure-encrypted-form]`.

== Frequently Asked Questions ==

= How to prevent and filter SPAM? =

The plugin has built in support for Cloudflare Turnstile. Create a free Turnstile site in your Cloudflare dashboard, then enable it in the plugin settings and paste the site key and the secret key. It is off by default.

= I get an error about my encryption key, but the key is fine =

Check that your site is served over HTTPS. Browsers only give access to the encryption API on secure connections, so on an HTTP site the form cannot encrypt anything. From version 1.3.0 the plugin tells you this directly, both in the admin and in the form.

= My server is not sending emails =

Your server may be restricted or disabled to send emails. In that case you can use a SMTP plugin to send authenticated emails as [WP Mail SMTP](https://es.wordpress.org/plugins/wp-mail-smtp/). Always remember to check your SPAM folder.

== Screenshots ==

1. Plugin settings: destination email, your OpenPGP public key and the diagnostic log level.
2. Optional spam protection with Cloudflare Turnstile, disabled by default.
3. The form as your visitors see it.
4. The same form with the Turnstile challenge enabled.
5. The diagnostic log, which never records email addresses or subjects.
6. Inserting the form in any page or post with a shortcode.

== Changelog ==

= 1.3.0 =
* Rewrote parts of the plugin description: why a private channel matters, and an honest list of what the plugin does not protect.
* Removed the donation panel, links and email footer. The donation page no longer exists.
* Added a "Delete all log files" button to the Debug log screen. Useful if you are upgrading from a version before 1.2.0, whose logs recorded the email addresses and subject of every message.
* The plugin now warns you when your site is not served over HTTPS. Browsers only allow encryption on secure connections, so on an HTTP site no message can be sent, and until now the only symptom was an error blaming your encryption key.
* The test email form now shows the underlying error instead of always reporting a problem with the public key.
* Added optional spam protection with Cloudflare Turnstile. It is disabled by default and needs both the site key and the secret key to switch on, so nothing changes unless you enable it.
* If Cloudflare cannot be reached, or your secret key is wrong, messages are allowed through and the problem is written to the diagnostic log. A Cloudflare outage never costs you a legitimate message.
* Your visitors' IP addresses are never sent to Cloudflare.

= 1.2.0 =
* Security: log files are no longer written to a predictable, publicly reachable path inside the uploads folder. The log directory now carries a random suffix and ships with server rules that block direct web access. Existing logs are moved to the protected location automatically. Reported by a plugin user, thank you.
* Security: the diagnostic log no longer records the sender email address or the message subject.
* Security: hardened the log viewer so it can only open the plugin's own log files.
* Added a "Diagnostic log" setting with three levels: disabled, errors only (the default) and full log. The log folder is only created when there is something to write.
* Logging is now handled by a single shared class instead of duplicated code in the admin and public sides.
* Security: the test email form is now restricted to administrators. Its endpoint was also registered for logged out visitors and shared a nonce with the public form, so anyone could trigger test emails.
* Fixed the form hanging on the spinner with no message when the encryption key has expired. An expired key fails when encrypting, not when it is read, and that case had no feedback.
* Fixed an unexpected mail error leaving the form without an answer: the visitor got no feedback and nothing was written to the log. Any failure is now reported and logged.
* Uninstalling the plugin now deletes its log files.
* Updated Monolog from 2.8.0 to 2.11.1, which removes the deprecation notices shown on PHP 8.4 and newer.
* Updated "Tested up to" to WordPress 7.1.
* Fixed the version number declared when enqueuing OpenPGP.js (it still said 5.5.0 while the bundled library is 6.3.0), so browsers pick up the right cached file.

= 1.1.0 =
* Updated OpenPGP.js from v5.5.0 to v6.3.0.
* Fixed silent encryption error when key is expired or invalid — now shows feedback to the user.
* Improved form field order: name, email, subject, message.
* Improved default form styling.
* Renamed admin submenu item to "Settings".
* Updated "Tested up to" to WordPress 7.0.
* Added "Requires PHP: 7.4".

= 1.0.1 =
* Fixed donation links.
* Added logs link on admin.
* Added ‘from’ and ‘to’ parameters on logs.
* Added detection for PHP mail() function.
* Updated feedback messages.
* Fixed initialization of plugin options, thanks to @nilovelez for commenting the problem.
* Fixed options leading spaces on inputs.
* Added loading status icon.

= 1.0.0 =
* Initial launch.

== Upgrade Notice ==

= 1.3.0 =
* Adds optional Cloudflare Turnstile spam protection, disabled by default.

= 1.2.0 =
* Security release: plugin logs are no longer web accessible and no longer store sender addresses or subjects. Please update. Also adds WordPress 7.1 compatibility.

= 1.1.0 =
* Updated OpenPGP.js to v6.3.0, fixed encryption error feedback, improved form UI and field order. Requires PHP 7.4+.

= 1.0.1 =
Fixed minor bugs, improved debug log, user feedback & UI/UX.

= 1.0.0 =
Initial launch.