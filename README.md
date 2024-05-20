# YoRus Behat Contexts

Provide some simple behat contexts

```
default:
    suites:
        default:
            contexts:
                - YoRus\BehatContexts\AmqpContext:
                    transports: 
                        # I intentionally did not use syntax %env()% because BEHAT doesn't fully
                        # support this case since Behat and Symfony kernel are not the sames.
                        async_internal: "env(MESSENGER_TRANSPORT_ASYNC_INTERNAL_DSN)"
                        my_second_queue: "DIRECT_DSN"
                    # you can define your own Adapter, it musts implements \WakeOnWeb\BehatContexts\AmqpAdapter\AdapterInterface;
                    # adapterClass: \WakeOnWeb\BehatContexts\AmqpAdapter\SymfonyMessengerAdapter
                    # Create queues if they don't exist.
                    # setupQueuesAutomatically: 1
                - YoRus\BehatContexts\FidryAliceFixturesContext:
                    # optional
                    # default is %kernel.project_dir%/tests/fixtures
                    # basepath: /var/www/.... 
                - YoRus\BehatContexts\DoctrineORMSchemaReloadContext
            paths:
                - tests/Features
```

## AmqpContext

If feature/scenario has tag @amqp, it'll automatically remove messages in all queues defined on context.


## FidryAliceFixturesContext

Needs [AliceDataFixtures](https://github.com/theofidry/AliceDataFixtures) and its bundle to be installed.

## DoctrineORMSchemaReloadContext

Needs [DoctrineBundle] to be installed.

If feature/scenario has tag @database, it'll automatically delete/create doctrine schema for all managers.

You have to create database before by yourself.
