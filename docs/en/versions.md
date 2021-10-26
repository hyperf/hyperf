# Versions

## Version Rules

Hyperf uses the version number rule of `x.y.z` to name each version, such as version 1.2.3, 1 is `x`, 2 is `y`, and 3 is `z`. You can make your update plan for the Hyperf framework according to the version rules.
- `x` indicates a major version. When the core of Hyperf undergoes a large number of refactoring changes, or when there are a large number of destructive API changes, it will be released as a version `x`. Generally speaking, version x changes cannot be compared with the previous version x Compatible, but it does not necessarily mean that it is completely incompatible. The specific identification is carried out according to the upgrade guide of the corresponding version.
- `y` represents an iterative version of a major function. When some public APIs undergo destructive changes, including changes or deletions of public APIs, which may cause the previous version to be incompatible, it will be released as version `y`.
- `z` means a fully compatible repair version. When bug fixes or security fixes are performed on the existing functions of each component, a version `z` will be selected for release. When a BUG causes a function to be completely unusable, it is also possible When fixing this BUG in version `z`, destructive changes were made to the API, but since the functions were completely unavailable before, such changes will not be released in version y. In addition to bug fixes, version `z` may also include some new features or Components, these functions and components will not affect the previous code usage.

## Upgrade

When you want to upgrade the Hyperf version, if it is an upgrade to the `x` or `y` versions, please follow the upgrade guide for the corresponding version in the document. If you want to upgrade the `z` version, you can directly execute the `composer update hyperf` command in the root directory of your project to update the dependent packages. We do not recommend that you upgrade the version of a certain component separately, but upgrade all components together to get a more consistent development experience.