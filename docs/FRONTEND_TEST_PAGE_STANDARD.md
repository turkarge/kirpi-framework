# Frontend Test Page Standardi

Bu dokuman, Kirpi Framework'de yeni bir frontend ozelligi eklendiginde
"ozellige ozel test sayfasi" akisini zorunlu ve tekrar edilebilir hale getirir.

## 1) Kapsam

Bu standart su tip frontend ozellikleri icin zorunludur:

- Yeni global JS API (`window.kirpi*`)
- Yeni ortak UI bileşeni (modal, notify, state, import/export gibi)
- Mevcut davranista tema/erişilebilirlik/interaction degisikligi

Uygulamaya ozel ekranlar bu standardin disindadir.

## 2) Zorunlu Cikti

Her yeni frontend ozelligi icin su 4 parcayi birlikte ekle:

1. Route:
- `/kirpi/<feature>-test`

2. Controller method:
- `AdminUiController::<feature>Test()`

3. Template:
- `core/Frontend/templates/admin/<feature>-test.php`

4. Feature test:
- `tests/Feature/FrontendUiKitTest.php` icinde yeni assertion bloğu

## 3) Isimlendirme Kurali

- Route: `/kirpi/<kebab-case>-test`
- Controller method: `<camelCase>Test`
- Template: `<kebab-case>-test.php`
- Navbar label: `<Title Case> Test`

Ornek:
- Route: `/kirpi/state-test`
- Method: `stateTest()`
- Template: `state-test.php`

## 4) Minimum Sayfa Icerigi

Her test sayfasi en az su bolumleri icermelidir:

- Ozellik basligi ve kisa amac metni
- En az bir "basarili senaryo" etkileşimi
- En az bir "hata/negatif senaryo" etkileşimi (mümkünse)
- JSON/log cikti alani (`pre`) veya gorunur durum feedback alani

## 5) Erişilebilirlik Minimumu

Test sayfasi su kosullari saglamalidir:

- Buton/ankor elemanlari klavye ile erişilebilir olmali
- Kritik aksiyonlar icin `aria-label` veya gorunur metin olmali
- Odak gecisinde `focus-visible` davranisi bozulmamis olmali

## 6) Uygulama Sablonu

### Route

```php
$router->get('/kirpi/<feature>-test', [\Core\Frontend\AdminUiController::class, '<feature>Test']);
```

### Controller

```php
public function <feature>Test(): Response
{
    $content = $this->render('admin/<feature>-test');

    $html = $this->renderTablerPage(
        title: 'Kirpi <Feature> Test',
        heroTitle: 'Kirpi <Feature> Test',
        heroSubtitle: '<Feature> davranis dogrulama sayfasi.',
        content: $content,
        currentPath: '/kirpi/<feature>-test'
    );

    return Response::make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
}
```

### Template

```php
<div class="card">
  <div class="card-header"><h3 class="card-title"><Feature> Test</h3></div>
  <div class="card-body">
    <button type="button" class="btn btn-primary" id="featureRunBtn">Calistir</button>
    <pre id="featureOutput" class="mt-3 mb-0 p-3 border bg-transparent">Henuz tetiklenmedi.</pre>
  </div>
</div>
<script>
(() => {
  const output = document.getElementById('featureOutput');
  document.getElementById('featureRunBtn')?.addEventListener('click', () => {
    output.textContent = JSON.stringify({ ok: true, ts: new Date().toISOString() }, null, 2);
  });
})();
</script>
```

### Feature Test

```php
public function test_<feature>_test_page_is_accessible(): void
{
    $response = $this->get('/kirpi/<feature>-test');

    $this->assertResponseStatus($response, 200);
    $this->assertStringContainsString('Kirpi <Feature> Test', $response->getContent());
    $this->assertStringContainsString('id="featureOutput"', $response->getContent());
}
```

## 7) PR / Push Checklist

- [ ] Route eklendi
- [ ] Controller method eklendi
- [ ] Template eklendi
- [ ] Navbar linki eklendi
- [ ] Feature test eklendi
- [ ] `phpunit --filter FrontendUiKitTest` gecti
- [ ] Full test gecti

## 8) Uygulama Karari

Bu dokuman Kirpi Framework icin zorunlu frontend test-sayfasi standardidir.
Yeni frontend cekirdek ozellikleri bu standard olmadan merge edilmemelidir.
