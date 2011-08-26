# JmikolaEventWildcardBundle

This bundle provides a way to assign event listeners using a wildcard pattern
inspired by AMQP topic exchanges.

Symfony2's event dispatcher component and the framework's existing convention
for event names (dot-separated words) is already quite similar to AMQP message
routing keys. This bundle is intended to be used sparingly, where wildcards may
replace verbose configuration for central listeners, such as an activity logging
service.

Some background info for this bundle may be found on the [symfony-devs][]
mailing list.

  [symfony-devs]: https://groups.google.com/d/topic/symfony-devs/GWeOTMfKg9s/discussion

## Documentation

This bundle's documentation lives in [Resources/doc/index.md][].

  [Resources/doc/index.md]: blob/master/Resources/doc/index.md
