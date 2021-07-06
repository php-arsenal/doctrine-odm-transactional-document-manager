# doctrine-odm-transactional-document-manager

Built upon Maciej [blog post](https://zgadzaj.com/development/mongodb/mongodb-multi-document-transactions-in-symfony-4-with-doctrine-and-mongodb-odm-bundle)

## Install

Require with Composer

```
composer require php-arsenal/doctrine-odm-transactional-document-manager 
```

Add to `services.yaml`

```yaml
    PhpArsenal\DoctrineOdmTransactionalDocumentManager\TransactionalDocumentManager:
        autowire: true
        autoconfigure: true
```

## Use

We might also wrap that `publishProducts()` code in a try-catch block and call `$this->documentManager->abortTransaction();` in its catch section, but if a transaction is not committed, it will be automatically aborted (rolled back), so there is no real need for that here.

```php
<?php

namespace YourNamespace;

use PhpArsenal\DoctrineOdmTransactionalDocumentManager\TransactionalDocumentManager;

class ProductManager
{
    protected TransactionalDocumentManager $documentManager;
 
    protected ObjectRepository $productRepository;
 
    public function __construct(TransactionalDocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
        $this->productRepository = $documentManager->getRepository(Product::class);
    }
 
    public function publishProducts()
    {
        $products = $this->productRepository->findBy(['published' => false]);
 
        $this->documentManager->startTransaction();
 
        foreach ($products as $product) {
            $product->setPublished(true);
        }
 
        $this->documentManager->flush([
            'session' => $this->documentManager->getSession(),
        ]);
 
        $this->documentManager->commitTransaction();
    }
}
```
