This is an extension to assign product to all other sources instead of default source

In a project, initially product was created by api without sources definition in bdody. 
So, initially products isassigned to default source. But should be assigned to all other custom sources, and unassigned on default

That's why, created observer on catalog_product_save_after event. Publish product sku into queue.

Then, consumer takes a product sku, unassign it from default source and assign to all other

Possible to make processing parallel: put consumer name "pineapple_development.assign.sources.product.create" in app/etc/env.php as part of 'cron_consumers_runner'=>'multiple_processes' elements of config array
Then, consumer will be run in multi threads through Rabbit MQ