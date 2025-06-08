# Laravel Entity Generator

A developer-friendly Laravel package to instantly generate fully-structured entities — including **Model, Controller, Repository (with Interface & Cache), Resource, Request, and Migration** — all based on a clean **Repository Pattern**. Built-in support for tagged caching makes it perfect for scalable Laravel APIs.

---

## ⚙️ Features

- ✅ One command to generate an entire entity stack
- ✅ Repository pattern structure with interface segregation
- ✅ Optional CacheRepository included for performance
- ✅ Generates:
    - Model (`extends BaseModel`)
    - Controller (API-ready)
    - Requests (Add, Update, All)
    - API Resource
    - Repositories: Interface + Eloquent + Cache
    - Migration
- ✅ Auto-binds interface to CacheRepository in your `RepositoryServiceProvider`
- ✅ Stub-driven — customize everything

---

## 🚀 Installation

```bash
    composer require alirezappeto/entity-generator
```

####  after installation the package you have to initialize the package utils
```bash
    php artisan entity-generator:install
```
This will generate:

- app/Models/BaseModel.php 
- app/Repository/BaseRepository.php
- app/Repository/BaseCacheRepository.php
- app/Repository/BaseRepositoryInterface.php
- and ...

---
## Generate an entity
```bash
   php artisan make:entity Product
```

#### this command will generate for you these files 
- app/Models/Product.php
- app/Http/Controllers/ProductController.php
- app/Http/Resources/ProductResource.php
- app/Http/Requests/Products/AddProductRequest.php
- app/Repositories/Product/ProductRepositoryInterface.php
- app/Repositories/Product/ProductRepository.php
- app/Repositories/Product/ProductCacheRepository.php
- database/migrations/xxxx_xx_xx_create_products_table.php
---

## 🧱 Repository Pattern
This package uses a clean layered architecture:

- Controller
-  → Request Validation
- → Repository Interface
- → Cache Repository
- → Base Repository (Eloquent)
---

## ⚠️ Cache Notes
Ensure your cache driver supports tags:
- ✅ CACHE_DRIVER=redis




