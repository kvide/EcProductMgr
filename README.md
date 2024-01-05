# EcProductMgr

This is a fork of the Products module for [CMS Made Simple](https://www.cmsmadesimple.org/). The module can co-exist and will not interfere with
systems that use the Products module.

## Installing

The module requires that the latest versions of CMSMSExt (v1.4.5) and SmartyExt (v1.3.0) modules are installed
on the server.

Download and unzip the latest EcProductMgr-x.x.x.xml.zip from [releases](../../releases). Use CMSMS's Module Manager
to upload the unzipped XML file to your server.

The module will only install on servers running CMSMS v2.2.19 on PHP 8.0 or newer. The software may run on older
versions of CMSMS or PHP, but the checks in MinimumCMSVersion() and method.install.php would need to be tweaked.

## Using the module

The module can be used as a standalone product management component in CMSMS or as a supplier module, providing product
and pricing information to the [EcommerceExt](../../../EcommerceExt) E-commerce extension.
