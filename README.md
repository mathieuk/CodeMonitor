# CodeMonitor

CodeMonitor allows you to monitor and alert on changes to specific functions, methods and/or entire classes. 

## Features

* Easily create watchlists of functions, methods or entire classes
* Namespace support
* Only get notifications of meaningful changes (not for whitespace or comment changes)

## Example usage
* Make sure your entire team is aware of changes to that sensitive method
* Keep track of changes to specific methods in that composer package you use


## Example

```
$ php codemon.php init 
Enter source base path [/Users/mathieu/Projects/CodeMonitor]: 
Enter directory to store my data in [/Users/mathieu/Projects/CodeMonitor/.codemon/]: 
Wrote database structure to /Users/mathieu/Projects/CodeMonitor/.codemon/sigs.db
Wrote configuration to /Users/mathieu/Projects/CodeMonitor/.codemon/config.json

$ php codemon.php watch -c ./.codemon/config.json Controller_Test
Found Controller_Test in /Users/mathieu/Projects/CodeMonitor/tests/src/controller/index.php. Watch this? [Y/n]: Y
[ADDED] Controller_Test

$ php codemon.php check -c ./.codemon/config.json
[CHANGED] /Users/mathieu/Projects/CodeMonitor/tests/src/controller/index.php: Controller_Test
--- Original
+++ New
@@ @@
 class Controller_Test
 {
     public function thisIsMyMethod()
     {
+        if (1) {
+            doSomethingUnsafe();
+        }
     }
 }
 
$ php codemon.php watch -c ./.codemon/config.json Controller_Test::thisIsMyMethod
Found Controller_Test::thisIsMyMethod in /Users/mathieu/Projects/CodeMonitor/tests/src/controller/index.php. Watch this? [Y/n]: Y
[ADDED] Controller_Test::thisIsMyMethod
```
