# Version Description

## Version Rules

Hyperf uses the x.y.z version numbering rule for all versions, e.g., version 1.2.3, where 1 is x, 2 is y, and 3 is z. You can formulate your update plan for the Hyperf framework based on this version rule.

- x represents a major version. When Hyperf's core undergoes significant refactoring, or when there are a large number of breaking API changes, it will be released as an x version. Changes in x versions are generally incompatible with previous x versions, but not necessarily completely incompatible. Specifics should be verified according to the upgrade guide for the corresponding version.
- y represents a major feature iteration version. When some public APIs have breaking changes, including changes or deletions of public APIs, resulting in possible incompatibility with previous versions, it will be released as a y version.
- z represents a fully compatible fix version. When bug fixes or security fixes are made to existing features of various components, they will be released as a z version. When a bug causes a feature to be completely unusable, it is also possible to make breaking changes to the API while fixing the bug in a z version. However, since the feature was already completely unusable, such changes will not be released as a y version. In addition to bug fixes, z versions may also include some new features or components. These features and components will not affect previous code usage.

## Upgrading Versions

When you wish to upgrade the Hyperf version, for upgrades to x and y versions, please follow the upgrade guide for the corresponding version in the documentation. If you wish to upgrade to a z version, you can directly execute the `composer update hyperf` command in the root directory of your project to update the dependency packages. We do not recommend that you upgrade a single component version individually, but rather upgrade all components uniformly to obtain a more consistent experience.
