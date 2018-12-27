# Hyperflex Coding Standard
Repository with all coding standard ruleset for Hyperflex repositories.

# Installation

1. Install the module via composer by running:

   ```bash
   $ composer require --dev hyperflex/coding-standard
   ```

2. Add composer scripts into your `composer.json`:

   ```json
   "scripts": {
     "cs-check": "phpcs",
     "cs-fix": "phpcbf"
   }
   ```

3. Create file `phpcs.xml` on base path of your repository with content:

   ```xml
   <?xml version="1.0"?>
   <ruleset name="Hyperflex Coding Standard">
       <rule ref="./vendor/hyperflex/coding-standard/ruleset.xml"/>

       <!-- Paths to check -->
       <file>config</file>
       <file>src</file>
       <file>test</file>
   </ruleset>
   ```

You can add or exclude some locations in that file.
For a reference please see: https://github.com/squizlabs/PHP_CodeSniffer/wiki/Annotated-ruleset.xml


# Usage

* To run checks only:

  ```bash
  $ composer cs-check
  ```

* To automatically fix many CS issues:
 
  ```bash
  $ composer cs-fix
  ```
