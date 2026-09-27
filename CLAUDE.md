# Secure Encrypted Form

WordPress plugin. The message is encrypted in the browser with OpenPGP.js and
only ever reaches the server already encrypted. Full documentation in
`README.md`.

## Commands

```bash
composer lint         # WordPress coding standards, must pass before a release
composer lint:fix     # auto-fix
composer test:crypto  # encryption tests (Node)
composer test         # both
./build.sh            # release zip
```

## Gotchas

- Composer's vendor dir is `lib/vendor`, not `vendor`.
- `readme.txt` is the WordPress.org plugin page, including the changelog.
  `README.md` is for developers. Keep the version in sync in three places: the
  plugin header, the `SECURE_ENCRYPTED_FORM_VERSION` constant and `Stable tag:`.
- The tests load `lib/js/openpgp.min.js`, the file that ships. When bumping
  OpenPGP.js, update the version passed to `wp_enqueue_script` in both the admin
  and public classes; a test asserts they match.
- Keys in `tests/fixtures/` are throwaway test keys, committed on purpose.
- Never log the message, the sender, the destination or the subject. Logs live
  in a randomly suffixed directory under uploads because that folder is web
  reachable.
- Releases are cut by pushing a git tag, which deploys to WordPress.org. Nothing
  runs on a normal push.
- Regenerate `languages/secure-encrypted-form.pot` with WP-CLI whenever a
  translatable string changes; the command is in `README.md`. It is easy to
  forget: the template had been stale since 1.0.1.
