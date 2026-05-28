# Starterkit V2 — Project Context

## Overview

Personal starter kit untuk memulai project Laravel + React dengan cepat. Sudah include autentikasi, manajemen user & role, permission-based access control, dan UI component library.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 12, Fortify, Inertia.js, Spatie Permission |
| Frontend | React 19, TypeScript 5.7, Tailwind CSS 4.0 |
| UI Components | Radix UI, Lucide Icons, Sonner (toast) |
| Build | Vite 7.0, Wayfinder (type-safe server actions) |
| Testing | Pest PHP 4 |

---

## Struktur Folder Penting

```
app/
├── Console/Commands/       # Custom artisan commands (make:feature, make:service)
├── Http/
│   ├── Controllers/        # Hanya HTTP layer — tidak ada logika bisnis
│   ├── Middleware/         # Inertia, Appearance
│   └── Requests/           # Form validation per resource
├── Models/                 # Eloquent models
└── Services/               # Semua logika bisnis

config/
└── starterkit.php          # Konfigurasi global: pagination, roles, permissions

database/
├── migrations/
└── seeders/                # PermissionSeeder & RoleSeeder baca dari config/starterkit.php

resources/js/
├── components/             # Reusable React components (Radix-based)
├── layouts/                # App layout, auth layout
├── pages/                  # Halaman per fitur (users/, roles/, settings/, auth/)
├── types/                  # TypeScript interfaces
└── hooks/                  # Custom React hooks

stubs/                      # Template kustom untuk artisan make:*
```

---

## Fitur yang Sudah Ada

- **Auth** — login, register, email verification, password reset, 2FA + recovery codes
- **User Management** — CRUD dengan role assignment, search, pagination
- **Role Management** — CRUD dengan permission assignment, search, pagination
- **Dashboard** — tampilan berbeda per role (admin vs member)
- **Settings** — profile, password, 2FA, appearance (dark/light mode)
- **RBAC** — Spatie Permission, permission dicek via middleware per controller action

---

## Skema Database

### Tabel Inti

```
users
├── id                        bigint PK
├── name                      string
├── email                     string UNIQUE
├── email_verified_at         timestamp nullable
├── password                  string (hashed)
├── two_factor_secret         text nullable
├── two_factor_recovery_codes text nullable
├── two_factor_confirmed_at   timestamp nullable
├── remember_token            string nullable
├── created_at / updated_at   timestamps
```

### Tabel RBAC (Spatie Permission)

```
permissions
├── id           bigint PK
├── name         string        # format: 'resource action' (cth: 'users index')
├── guard_name   string        # default: 'web'
└── timestamps

roles
├── id           bigint PK
├── name         string        # cth: 'admin', 'user'
├── guard_name   string        # default: 'web'
└── timestamps

role_has_permissions          # pivot: role ↔ permission
├── permission_id FK → permissions.id
└── role_id       FK → roles.id

model_has_roles               # pivot: user ↔ role
├── role_id      FK → roles.id
├── model_type   string        # 'App\Models\User'
└── model_id     bigint        # users.id

model_has_permissions         # pivot: user ↔ permission (direct assignment)
├── permission_id FK → permissions.id
├── model_type    string
└── model_id      bigint
```

### Tabel Sistem (Laravel built-in)

```
sessions             # user sessions
password_reset_tokens
cache
jobs
```

### Relasi Antar Entitas

```
User ──< model_has_roles >── Role ──< role_has_permissions >── Permission
User ──< model_has_permissions >── Permission  (direct, jarang dipakai)
```

### Catatan Penting untuk Desain Database Baru

- Setiap tabel yang berelasi ke `users` gunakan FK `user_id → users.id`
- Permission format: `'<resource> <action>'` — didefinisikan di `config/starterkit.php`
- Jika role tertentu hanya boleh akses data miliknya sendiri, tambahkan kolom `user_id` di tabel resource tersebut dan filter di Service layer
- Tidak ada soft delete — data yang dihapus langsung hilang dari DB

---

## Arsitektur: Service Pattern

Controller hanya sebagai HTTP layer. Semua logika bisnis ada di Service.

```
Request → Controller → Service → Model
```

**Controller** — terima input, panggil service, return response:
```php
public function store(StoreProductRequest $request)
{
    $this->productService->create($request->validated());
    return redirect()->route('products.index')->with('success', '...');
}
```

**Service** — logika bisnis, query, operasi DB:
```php
public function create(array $data): Product
{
    return Product::create($data);
}
```

**Aturan:**
- Controller tidak boleh berisi query Eloquent langsung
- Service tidak boleh berisi `request()`, `redirect()`, atau `session()`
- Semua controller implement `HasMiddleware` dari Spatie Permission

---

## Role-Based Data Filtering

Ketika satu resource perlu ditampilkan berbeda per role, filtering dilakukan di Service:

```php
// Contoh: admin lihat semua, member hanya lihat miliknya
public function getAll(?string $search, User $user): LengthAwarePaginator
{
    return Booking::query()
        ->when(!$user->hasRole('admin'), fn($q) => $q->where('user_id', $user->id))
        ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
        ->paginate(config('starterkit.pagination'));
}
```

Dashboard juga menerapkan pola ini — `DashboardController` return view berbeda berdasarkan role user.

---

## Konfigurasi Global (`config/starterkit.php`)

```php
'pagination'         => 8,
'roles'              => ['admin', 'user'],
'default_admin_role' => 'admin',
'permissions'        => [
    'users index'  => 'View Users',
    'users create' => 'Create User',
    // format: 'resource action' => 'Human Readable Label'
],
```

Ketika menambah fitur baru, tambahkan permission-nya di sini **sebelum** membuat controller. Label digunakan untuk tampilan di frontend (form assign permission), nama teknis digunakan untuk middleware.

---

## Format Permission

```
'<resource> <action>' => '<label>'

// Contoh:
'products index'  => 'View Products'
'products create' => 'Create Product'
'products edit'   => 'Edit Product'
'products delete' => 'Delete Product'
```

Permission di-seed otomatis dari `config/starterkit.php` via `PermissionSeeder`. Role `admin` otomatis mendapat semua permission.

---

## Pola Kerja untuk Fitur Baru

Urutan ini wajib diikuti karena setiap langkah jadi dependency langkah berikutnya:

```
1. config/starterkit.php  → tambah permissions baru
2. php artisan make:feature NamaFitur  → generate semua file sekaligus
3. routes/web.php         → tambah Route::resource(...)
4. Migration              → isi kolom tabel
5. Request                → isi validation rules
6. Service                → isi logika bisnis
7. Controller             → inject service, isi method
8. resources/js/types/    → buat TypeScript interface
9. resources/js/pages/    → buat halaman React (index, create, edit)
```

---

## Custom Artisan Commands

```bash
# Generate lengkap satu fitur: model + migration + controller + request + service
php artisan make:feature Product

# Generate service saja (jika tidak butuh full feature)
php artisan make:service Product
# atau dengan suffix eksplisit:
php artisan make:service ProductService
```

`make:feature` otomatis menampilkan checklist langkah selanjutnya setelah selesai generate.

---

## Middleware Permission di Controller

Setiap controller resource wajib menggunakan pola ini:

```php
class ProductController extends Controller implements HasMiddleware
{
    public function __construct(private ProductService $productService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:products index',  only: ['index']),
            new Middleware('permission:products create', only: ['create', 'store']),
            new Middleware('permission:products edit',   only: ['edit', 'update']),
            new Middleware('permission:products delete', only: ['destroy']),
        ];
    }
}
```

Nama permission harus **persis sama** dengan key yang ada di `config/starterkit.php`.

---

## Seeder

```bash
php artisan db:seed
```

Menjalankan: `PermissionSeeder` → `RoleSeeder` → `UserSeeder`

- Permissions dibuat dari `config/starterkit.php`
- Role `admin` dan `user` dibuat otomatis
- Role `admin` mendapat semua permissions

---

## Catatan Frontend

- Semua halaman menggunakan Inertia.js — tidak ada API JSON terpisah
- Props dari controller langsung tersedia sebagai TypeScript types
- Wayfinder generate type-safe route references otomatis setiap `npm run dev`
- Toast notification menggunakan Sonner
- Dark mode dikelola via `useAppearance` hook + `next-themes`
