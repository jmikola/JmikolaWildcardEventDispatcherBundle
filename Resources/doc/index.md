# JmikolaEventWildcardBundle

This bundle provides a way to assign event listeners using a wildcard pattern
inspired by AMQP topic exchanges.

Symfony2's event dispatcher component and the framework's existing convention
for event names (dot-separated words) is already quite similar to AMQP message
routing keys. This bundle is intended to be used sparingly, where wildcards may
replace verbose configuration for central listeners, such as an activity logging
service.

## Installation

### Submodule Creation

Add EventWildcardBundle to your `vendor/` directory:

``` bash
$ git submodule add https://github.com/jmikola/JmikolaEventWildcardBundle.git vendor/bundles/Jmikola/EventWildcardBundle
```

### Class Autoloading

Register the "Jmikola" namespace prefix in your project's `autoload.php`:

``` php
# app/autoload.php

$loader->registerNamespaces(array(
    'Jmikola' => __DIR__'/../vendor/bundles',
));
```

### Application Kernel

Add EventWildcardBundle to the `registerBundles()` method of your application
kernel:

``` php
# app/AppKernel.php

public function registerBundles()
{
    return array(
        new Jmikola\EventWildcardBundle\JmikolaEventWildcardBundle(),
    );
}
```

## Configuration

There are no configuration options. Symfony2 will load the bundle's dependency
injection extension automatically.

The extension will create a service that [composes][] the existing
`event_dispatcher` service and assumes its service ID. Depending on whether
debug mode is enabled, this bundle may wrap an instance of FrameworkBundle's
`ContainerAwareEventDispatcher` or `TraceableEventDispatcher` class.

  [composes]: http://en.wikipedia.org/wiki/Object_composition

## Listening on Wildcard Event Patterns ##

### Single-word Wildcard ###

Consider the scenario where the `my.listener` service is currently listening on
multiple `core` events:

``` xml
# Resources/config/my_listener.xml

<service id="my.listener" class="MyListener">
    <tag name="kernel.listener" event="core.exception" method="trackCoreEvent" />
    <tag name="kernel.listener" event="core.request" method="trackCoreEvent" />
    <tag name="kernel.listener" event="core.response" method="trackCoreEvent" />
</service>
```

In this example `trackCoreEvent()` is a listener method that should observe all
`core` events in the application. Perhaps it needs to log some details about
these events to an external statistics API.

With this bundle enabled, you can the single-word `*` wildcard with the
following syntax:

``` xml
# Resources/config/my_listener.xml

<service id="my.listener" class="MyListener">
    <tag name="kernel.listener" event="core.*" method="trackCoreEvent" />
</service>
```

The `trackCoreEvent()` listener will now observe all events named `core` or
starting with `core.` and followed by another word.

### Multi-word Wildcard ###

Suppose there was a `core` event in your application named `core.foo.bar`. The
aforementioned `core.*` syntax would not catch this event. You could use:

``` xml
# Resources/config/my_listener.xml

<service id="my.listener" class="MyListener">
    <tag name="kernel.listener" event="core.*.*" method="trackCoreEvent" />
</service>
```

This syntax would match `core.foo` and `core.foo.bar`, but `core` would no
longer be matched (assuming there was such an event).

The multi-word `#` wildcard might be more appropriate here:

``` xml
# Resources/config/my_listener.xml

<service id="my.listener" class="MyListener">
    <tag name="kernel.listener" event="core.#" method="trackCoreEvent" />
</service>
```

Suppose there was also an `trackAnyEvent()` method in `my.listener` that needed
to listen on _all_ events in the application. The multi-word `#` wildcard could
be used as so:

``` xml
# Resources/config/my_listener.xml

<service id="my.listener" class="MyListener">
    <tag name="kernel.listener" event="#" method="trackAnyEvent" />
</service>
```

### Additional Wildcard Documentation ###

When in doubt, the unit tests for `ListenerPattern` are a good resource for
determining how wildcards will be interpreted. This bundle aims to mimic the
behavior of AMQP topic wildcards completely, but there may be shortcomings.

Documentation for actual AMQP syntax may be found in the following packages:

 * [ActiveMQ](http://activemq.apache.org/wildcards.html)
 * [HornetQ](http://docs.jboss.org/hornetq/2.2.5.Final/user-manual/en/html/wildcard-syntax.html)
 * [RabbitMQ](http://www.rabbitmq.com/faq.html#wildcards-in-topic-exchanges)
 * [ZeroMQ](http://www.zeromq.org/whitepapers:message-matching)
