Current version: 0.0.1

Prestashop minimum requirements
myShopRepair has a tool which allows you to check your web-shop configuration in seconds.
This way you can see if you have the required PHP version and MYSQLI extension. This tool will also check if you have set your php.ini correctly and give you the required and recommended values.
It will also check if you have activated all necessary PHP extensions and check if the required folders have the right writing permissions.

Clear cache and reset .htaccess:
myShopRepair can delete your cache folder in seconds, this way you can remove your cache without needing to connect to your FTP client. This feature comes in handy when you have lost access to your back-office.

You will also have the option to reset your .htaccess file, This could be needed when moving to a new shop or when you have troubles with Friendly URL function.
The reset .htaccess function will only reset the lines between the Prestashop comment (# ~~start~~ lines lines  lines # ~~end~~). This way it prevents your store losing important settings.

Web-shop to a new domain:
myShopRepair will help you when you have moved your web-shop to a new domain and did not change the required fields. Our tool will allow you to change your Shop domain name, SSL domain and Base URI which are required parameters to change when moving to a new domain. Our tool will allow you to make these changes without having to run MYSQL codes your self.

Shop Maintenance mode
When your web-shop is running in troubles and you have lost access to your back-office you might want to disable your web-shop temporally.
myShopRepair gives you the option to enable and disable your web-shop into maintenance mode. You are also able to add or remove an maintenance IP-address.
The advantage of the myShopRepair tool is that you do not need to run a SQL query manually to enable or disable your web-shop.

SSL Options
Using myShopRepair will also give you the opportunity  to enable or disable SSL on your web-shop. This comes in handy when switching to SSL failed and you have lost your back-office accessibility.

Automatic version checker:
myShopRepair tool comes with an automatic version checker. This way you will be informed if you are running a myShopRepair version which is outdated.

DEMO:
You can view a demo of this file here

INFORMATION:
The file myshoprepair.php needs to be placed where your prestashop web-shop is installed. for example 'mystore.com/myshoprepair.php'.

File save location:

/yourstore/
--- /Adapter/
--- /app/
--- /bin/
--- ...
------ /.htaccess
------ /autoload.php
------ /myshoprepair.php <-- Place our tool in your web-shop root
------ ...
