# Microservice

Microservices are small and autonomous services that work together.

## Small, focus on doing one thing well

With the iteration of requirements and the increase of new features, the code repository tends to become larger and larger. Although we strongly hope to achieve clear modularity in the huge code repository, in fact, the boundaries between modules are difficult to distinguish clearly. Gradually, similar functional codes can be seen everywhere in the code repository. As a result, it is very difficult to know where to make changes when edition updates, and it is increasingly difficult to fix `Bug` and add new features.
In a mono-system, some abstraction layers or modularization are usually created to ensure the `cohesion` of the code, thereby avoiding the problems mentioned above.

> According to Robert C. Martin [Single Responsibility Principle](https://baike.baidu.com/item/单一职责原则/9456515): "* Put things that change for the same reason together and separate things that change for different reasons. *" This argument emphasizes the concept of `cohesion` very well.

Microservices apply this concept to independent services, and determine the boundaries of services based on the boundaries of the business. Each service focuses on things within the boundaries of the service. By doing so, can we avoid many problems arising from excessively large code repository.
How tiny should a microservice be? Small enough, but not too small. 
How to evaluate whether a system is disassembled small enough? When you don't have the desire to make it smaller in the entire system, then it should be small enough. The smaller the services, the more obvious the advantages and disadvantages of `Microservice`. The smaller the service used, the greater the benefits of independence, but the management of a large number of services will also be more complicated.

## Autonomy

A microservice is an independent entity, it can be deployed independently, and it can also exist as an operating system process. There is isolation between services, and services are communicated through network, thereby strengthening the isolation between services and avoiding tight coupling. Services should be able to be modified independently, and the deployment of a certain service should not cause changes in the `Service Consumer`. This requires us to consider how much of these `Service Providers` should be exposed and what should be hidden. If they are exposed too much, the `Service Consumer` will be coupled with the internal implementation of the providers. This will make the service directly generate additional coordination work, thereby reducing the autonomy of the service.

## Main benefits

### Heterogeneity of technology

In a system where multiple services cooperate with each other, the technology which is most suitable for the service can be selected from different services. Because the services are called through the network, the realization of the service will not be limited by the implementation language or system framework. This means that when a part of the system needs performance improvement, the implementation of that part can be rebuilt using a better-performing technology stack.

### Elasticity

A key concept to realize a elastic system is `Bulkhead`. If a component or a service in the system is unavailable, but does not cause a cascading failure, then other parts of the system can still operate normally. The `service boundary` of microservice is obviously a `Bulkhead`. In the `Monolithic architecture` system, that is, the system under the traditional `PHP-FPM` architecture, if a certain part is unavailable, then all functions are unavailable in most cases. Although the system can be deployed on multiple nodes through technologies such as load balancing to reduce the probability that the system is completely unavailable, for the `Microservice` system, the architecture itself can handle service unavailability and issues such as functional degradation.

### Expansibility

A `monolithic architecture` system can only be expanded as a whole, even if only a small part of the system has performance problems. If you use multiple smaller services, you can only extend the services which need to be extended, so that those services which do not need to be extended can be run on cheaper servers and saving costs.

### Simply deployment

In a `monolithic architecture` system with a huge amount of code, even if only one line of code is modified, the entire system needs to be redeployed to publish the change. This kind of deployment has a great impact and high risk, so related persons rarely do such deployment. Therefore, the frequency of deployment in actual operations become very low. A lot of features or `Bugfix` will be made to the system between versions, and a large number of changes will be released to the production environment at one time. But the greater the difference between the two releases, the greater the likelihood of errors.
Of course, in the development under the traditional `PHP-FPM` architecture, we may not have such a problem, because hot updates are a natural existence. However, the pros and cons exist at the same time.

### Match with the organizational structure

In the case of `Monolithic architecture` and the structure of the team is also 'distributed' (remote), code conflicts caused by a large number of engineers' code submissions and iterative communication in different places will make the maintenance system more complex. As we all know that a team with appropriate size can get higher productivity by working on a small repository, so the division of services can well divide the related responsibilities.

### Composability

The main benefit claimed by `Distributed System` and `Service Oriented Architecture (SOA)` is that it is easy to reuse existing functions. Under the `Microservice`, more fine-grained service splitting will reflect this advantage more vividly.

### Highly reconfigurable

If you are facing a large `monolithic architecture` system, the code inside is messy, and everyone is afraid to refactor. But when you are dealing with a small-scale fine-grained service, refactoring a service or even rewriting a corresponding service is relatively operable.
In a large `monolithic architecture` system, can you be sure that it will not cause any problems with hundreds of lines of code are deleted in one single day? But with a good `Microservice`, I believe that you can delete a service directly without any problem.

## No Silver Bullet

Although the benefits of `Microservices` are numerous, however, **Microservice is not a silver bullet! ! !**. You need to consider the complexity that all distributed systems need to consider. You may need to do a lot of work on deployment, testing, monitoring, calls between services, and service reliability, and even you need to deal with issues similar to distributed transactions or CAP-related issues. Although `Hyperf` has solved many problems for you, your team must have enough knowledge related to distributed systems before implementing `Microservice`, in order to deal with problems that you may never face or considered.

*| Part of the content in this chapter is referred from《Building Microservices》 by Sam Newman*