=== Secure Encrypted Form ===
Contributors: danidub
Donate link: https://charrua.es/donaciones/
Tags: contact, form, contact form, openpgp, encrypted form, feedback, email, encryption, secure, secure form
Requires at least: 5.3
Tested up to: 6.4.3
Stable tag: 1.0.1
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

* Message
* Subject
* Name
* Email

= How it works =
The *message* field will be encrypted with your **PGP public key** and sent as an attachment in **ASCII** format to the destination email you have configured.

When creating the plugin logic I have made sure that the *message* field **is never sent to the web server**, the data is previously encrypted (on the fly) using *OpenPGP.js* library in the user who is browsing the website.

You will only be able to decrypt the content of the attached file if you have the PGP private key belonging to the public key with which the message was encrypted.

*Remember that the purpose of the plugin is only to display a form on your website and encrypt the information that is sent through the "message" field. This plugin does not take care of decrypting the attached file, this task is left to each user in the way they want.*

= Some usage examples =

* Receive secret messages
* Receiving passwords from clients or friends
* Reception of sensitive information

= Requirements =

In order to use this plugin you need to have or create a **PGP key pair**. If you don't have your key pair generated you can browse the internet on how to generate it.
There are many ways to generate the key, each have a different impact on security.

= TIP on generating PGP key pair =
One of the best ways of generating your PGP key pair is using a computer witout Internet connection and using [Tails OS](https://tails.boum.org).

= Recommended software =

* [GNU Privacy Guard (Linux, OS X, Windows)](https://gnupg.org)
* [GPG Suite (OS X)](https://gpgtools.org)
* [Gpg4win (Windows)](https://www.gpg4win.org/)

= Support =

When you cannot find the answer to your question on the FAQ section, check the [support forum](https://wordpress.org/support/plugin/secure-encrypted-form/) on WordPress.org. If you cannot locate any topics that solve to your particular issue, post a new topic for it.
Remember this support is offered for free and can take some hours/days to answer and solve your issues.

= Secure Contact Form needs your support =

It is hard to continue development and support for this free plugin without contributions from users like you. **If you enjoy using Secure Contact Form and find it useful, please consider [making a donation](https://charrua.es/donaciones/)**. Your donation will help encourage and support the plugin's continued development and better user support.

= Privacy notices =

With the default configuration, this plugin, in itself, does not:

* Track users by stealth
* Write any user personal data to the database
* Send any data to external servers
* Use cookies

= Translations =

Actually the plugin ships in English and is translated to Spanish.
You can contribute and [translate this plugin to your own language](https://translate.wordpress.org/projects/wp-plugins/secure-encrypted-form/).

== Installation ==

1. Upload the entire `secure-encrypted-form` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the **Plugins** screen (**Plugins > Installed Plugins**).

You will find **Secure Encrypted Form** menu in your WordPress admin screen.

== Frequently Asked Questions ==

= How to prevent and filter SPAM? =

You can use some service like Google Recaptcha v3 for now. More comming soon.

= My server is not sending emails =

Your server may be restricted or disabled to send emails. In that case you can use a SMTP plugin to send authenticated emails as [WP Mail SMTP](https://es.wordpress.org/plugins/wp-mail-smtp/). Always remember to check your SPAM folder.

== Screenshots ==

1. Plugin options
2. Plugin debug log
3. Inserting through shortcode
2. Form rendered

== Changelog ==

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

= 1.0.1 =
Fixed minor bugs, improved debug log, user feedback & UI/UX.

= 1.0.0 =
Initial launch.