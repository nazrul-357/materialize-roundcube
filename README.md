# materializerc
Materialize CSS skin for Roundcube, optimized for mobile view.

This repository consists of two important directories to run the materialized view for Roundcube; **skins** and **plugins**.

## Skins
This directory contains **materialize** skin which must be put in the **skins** directory of Roundcube. The **materialize** **skin** and the **plugin** must be enabled manually or, for the skin, set as default in Roundcube's config.

```php
...
$config['skin']='materialize';
```

## Plugin
This directory contains **materialize** plugin which must be put in the **plugins** directory of Roundcube. The **materialize** **skin** and the **plugin** must be enabled manually or, for the skin, set as default in Roundcube's config.

```php
...
$config['plugins'] = array(
  ...  
	'materialize',	
);
```
