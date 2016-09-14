# JmikolaWildcardEventDispatcherBundle

[![Build Status](https://travis-ci.org/jmikola/JmikolaWildcardEventDispatcherBundle.png?branch=master)](https://travis-ci.org/jmikola/JmikolaWildcardEventDispatcherBundle)

This bundle integrates the [WildcardEventDispatcher][] library with Symfony2 and
allows event listeners to be assigned using a wildcard pattern inspired by
AMQP topic exchanges.

Symfony2's event dispatcher component and the framework's existing convention
for event names (dot-separated words) is already quite similar to AMQP message
routing keys. This bundle is intended to be used sparingly, where wildcards may
replace verbose configuration for central listeners, such as an activity logging
service.

Some background info for this bundle may be found on the [symfony-devs][]
mailing list.

  [WildcardEventDispatcher]: https://github.com/jmikola/WildcardEventDispatcher
  [symfony-devs]: https://groups.google.com/d/topic/symfony-devs/GWeOTMfKg9s/discussion

## Compatibility

This bundle requires Symfony 2.3 or above.

## Configuration

There are no configuration options. Symfony2 will load the bundle's dependency
injection extension automatically.

The extension will create a service that [composes][] the existing
`event_dispatcher` service and assumes its service ID. Depending on whether
debug mode is enabled, this bundle may wrap an instance of FrameworkBundle's
`ContainerAwareEventDispatcher` or `TraceableEventDispatcher` class.

  [composes]: http://en.wikipedia.org/wiki/Object_composition

## Listening on Wildcard Event Patterns ##

This bundle enables you to use the single-word `*` and multi-word `#` wildcards
when assigning event listeners. The wildcard operators are described in greater
detail in the documentation for [WildcardEventDispatcher][].

### Single-word Wildcard ###

Consider the scenario where the `acme.listener` service is currently listening
on multiple `core` events:

```xml
<!-- Acme/MainBundle/Resources/config/listener.xml -->

<service id="acme.listener" class="Acme/MainBundle/Listener">
    <tag name="kernel.listener" event="core.exception" method="onCoreEvent" />
    <tag name="kernel.listener" event="core.request" method="onCoreEvent" />
    <tag name="kernel.listener" event="core.response" method="onCoreEvent" />
</service>
```

You could refactor the above configuration to use the single-word `*` wildcard:

```xml
<!-- Acme/MainBundle/Resources/config/listener.xml -->

<service id="acme.listener" class="Acme/MainBundle/Listener">
    <tag name="kernel.listener" event="core.*" method="onCoreEvent" />
</service>
```

This listener will now observe all events starting with `core.` and followed by
another word. An event named `core` would also be matched by this pattern.

### Multi-word Wildcard ###

Suppose there was an event in your application named `core.foo.bar`. The
aforementioned `core.*` pattern would not match this event. You could use:

```xml
<!-- Acme/MainBundle/Resources/config/listener.xml -->

<service id="acme.listener" class="Acme/MainBundle/Listener">
    <tag name="kernel.listener" event="core.*.*" method="onCoreEvent" />
</service>
```

This syntax would match `core.foo` and `core.foo.bar`, but `core` would no
longer be matched (assuming there was such an event); however, the multi-word
`#` wildcard would allow all of these event names to be matched:

``` xml
<!-- Acme/MainBundle/Resources/config/listener.xml -->

<service id="acme.listener" class="Acme/MainBundle/Listener">
    <tag name="kernel.listener" event="core.#" method="onCoreEvent" />
</service>
```

The `#` wildcard could also be used to listen to _all_ events in the
application:

``` xml
<!-- Acme/MainBundle/Resources/config/listener.xml -->

<service id="acme.listener" class="Acme/MainBundle/Listener">
    <tag name="kernel.listener" event="#" method="onAnyEvent" />
</service>
```
