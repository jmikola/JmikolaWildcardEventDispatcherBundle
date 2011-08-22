# EventWildcardBundle

This bundle provides a way to assign event listeners using a wildcard pattern
inspired by AMQP topic exchanges.

Symfony2's Event Dispatcher component and the framework's existing convention
for event names (dot-separated words) is already quite similar to AMQP message
routing keys. This bundle is intended to be used sparingly, where wildcards may
replace verbose configuration for central listeners, such as an activity logging
service.

Documentation for the wildcard syntax (and topic exchanges in general) may be
found in [ActiveMQ][], [HornetQ][], [RabbitMQ][] and [ZeroMQ][]. Some background
info for this bundle can be found on the [symfony-devs][] mailing list.

  [ActiveMQ]: http://activemq.apache.org/wildcards.html
  [HornetQ]: http://docs.jboss.org/hornetq/2.2.5.Final/user-manual/en/html/wildcard-syntax.html
  [RabbitMQ]: http://www.rabbitmq.com/faq.html#wildcards-in-topic-exchanges
  [ZeroMQ]: http://www.zeromq.org/whitepapers:message-matching
  [symfony-devs]: https://groups.google.com/d/topic/symfony-devs/GWeOTMfKg9s/discussion

Additional documentation for this bundle is forthcoming.
