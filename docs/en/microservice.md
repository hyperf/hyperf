# Microservices

Microservices are small, autonomous services that work together.

## Small, Focused on Doing One Thing Well

As requirements iterate and new features are added, codebases often grow increasingly large. Even with our best efforts to achieve clear modularity within a massive codebase, in reality, boundaries between modules are hard to define. Over time, similar functional code becomes pervasive, making it difficult to know where to make modifications during iteration, and progressively harder to fix bugs or add new features.
In a monolithic system, we usually create abstraction layers or implement modularity to ensure code `cohesion`, thus avoiding the problems mentioned above.

> According to Robert C. Martin's argument on the [Single Responsibility Principle](https://baike.baidu.com/item/单一职责原则/9456515): "*Gather together the things that change for the same reasons. Separate things that change for different reasons.*" This argument well emphasizes the concept of cohesion.

Microservices apply this philosophy to independent services, determining service boundaries based on business boundaries. Each service focuses on what lies within its boundary, thus avoiding many issues stemming from excessively large codebases.
So, how small should a microservice be? Small enough is fine, but not too small. How do you measure if a system is split small enough? When you face the system and no longer have the desire to "break it down" because it's too large, it should be small enough. The smaller the service, the more obvious the advantages and disadvantages of `Microservice architecture` become. Smaller services bring more benefits from independence, but managing a large number of services also becomes more complex.

## Autonomy

A microservice is an independent entity that can be deployed independently and exist as an operating system process. There is isolation between services, and communication between services occurs via network calls, thereby strengthening isolation and avoiding tight coupling. Services should be able to be modified independently of each other, and the deployment of one service should not cause changes in its `Service Consumer`. This requires us to consider what the `Service Provider` should expose and what it should hide. If too much is exposed, the `Service Consumer` will couple with the internal implementation of the service, which will cause additional coordination work for the service, thereby reducing its autonomy.

## Main Benefits

### Technical Heterogeneity

In a system consisting of multiple collaborating services, you can select the most suitable technology for each service. Since communication between services is via network calls, the implementation of a service is not limited to the system's implementation language or framework. This means that when a part of the system needs performance improvement, that part can be rebuilt using a technology stack with better performance.

### Resilience

A key concept for achieving a resilient system is the `Bulkhead`. If a component or service in the system becomes unavailable, it should not lead to a cascading failure, allowing the rest of the system to function normally. The `service boundary` of a microservice is clearly a `Bulkhead`. In a `Monolithic architecture` system, specifically under the traditional `PHP-FPM` architecture, if a part is unavailable, in most cases, all functionality is unavailable. Although you can use load balancing and other technologies to deploy the system on multiple nodes to reduce the probability of the system being completely unavailable, for a `Microservice architecture` system, its architecture itself can well handle service unavailability and functional degradation.

### Scalability

A `Monolithic architecture` system can only be scaled as a whole, even if only a small part of the system has performance issues. By using multiple smaller services, you can scale only the services that need scaling, allowing services that do not need scaling to run on cheaper servers, thus saving costs.

### Simplified Deployment

In a `Monolithic architecture` system with a massive amount of code, even if you only change one line of code, you need to redeploy the entire system to release the change. Such deployment has a significant impact and high risk; therefore, stakeholders involved are afraid to deploy easily. As a result, in practical operations, the frequency of deployment becomes very low. Many features or `Bugfixes` are accumulated between versions, and a large number of changes are released to the production environment at once. However, the greater the difference between two releases, the greater the possibility of errors.
Of course, in development under the traditional `PHP-FPM` architecture, we might not have such a problem, because hot updates exist naturally, but pros and cons exist simultaneously.

### Alignment with Organizational Structure

In a `Monolithic architecture`, especially when the team structure is "distributed" (geographically dispersed), code conflicts caused by a large number of engineers' code submissions and remote iteration communication make maintaining the system more complex. We all know that a suitably sized team working on a small codebase can achieve higher productivity. Therefore, the splitting and ownership of services can well divide related responsibilities.

### Composability

A major claimed benefit of `Distributed systems` and `Service-Oriented Architecture (SOA)` is the ease of reusing existing functionality. Under `Microservice architecture`, finer-grained service splitting will make this advantage even more prominent.

### High Refactorability

If you are facing a large `Monolithic architecture` system with chaotic and ugly code, everyone is afraid to refactor it easily. But when you face a small-scale, fine-grained service, refactoring a service or even rewriting a corresponding service is relatively actionable. Can you be sure that deleting hundreds of lines of code in a large `Monolithic architecture` system in one day won't cause any problems? But in a well-designed `Microservice architecture`, I believe you can also handle deleting a service with ease.

## No Silver Bullet

Although `Microservice architecture` has many benefits, **Microservices are not a silver bullet!!!** You need to face the complexity that all distributed systems must face. You may need to do a lot of work in deployment, testing, and monitoring, and a lot of work in inter-service calls and service reliability. You may even need to handle issues similar to distributed transactions or those related to CAP. Although `Hyperf` has solved many problems for you, before implementing `Microservice architecture`, your team must possess sufficient knowledge of distributed systems to face many problems you may not have faced or considered under `Monolithic architecture`.


*| Some content of this chapter is translated from Sam Newman's "Building Microservices"*
