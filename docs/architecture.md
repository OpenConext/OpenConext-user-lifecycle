# Application architecture

In order for the application to be developed and tested in isolation from its infrastructural dependencies, and to
achieve a clean separation of concerns, the application architecture is based on
[Hexagonal Architecture](http://alistair.cockburn.us/Hexagonal+architecture).

Source code is divided into three layers:

1. The **domain** contains the business logic, encapsulated in entities and value objects. It protects its own state so
that it always contains valid data.
2. The **application** layer is a thin layer around the domain and contains entry points to interact with the domain,
such as commands and command handlers.
3. The **infrastructure** layer contains all infrastructural code, such as repository implementations, HTTP controllers,
etc. These can be seen as the adapters from ports & adapters.

These layers are represented as namespaces inside the `OpenConext\UserLifecycle` namespace.

This architecture allows for instance to test the domain- and application code without having to make calls to the
outside world (database, other applications, etc.) which significantly speeds up the execution of the test suite.