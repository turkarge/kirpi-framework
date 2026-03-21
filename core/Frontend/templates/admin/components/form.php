<form class="ui-form" action="javascript:void(0)">
    <label>
        Urun Adi
        <input type="text" placeholder="Orn: Domates Corbasi">
    </label>
    <label>
        Kategori
        <select>
            <option>Seciniz</option>
            <option>Teklif</option>
            <option>Recete</option>
            <option>Icerik</option>
        </select>
    </label>
    <div class="inline">
        <button class="btn btn-primary" type="submit">Kaydet</button>
        <button class="btn btn-ghost" type="button">Taslak</button>
    </div>
</form>
<style>
    .ui-form { display: grid; gap: 12px; max-width: 500px; }
    .ui-form label { display: grid; gap: 6px; font-weight: 600; font-size: 14px; }
    .ui-form input,
    .ui-form select {
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 10px 11px;
        font: inherit;
        color: inherit;
        background: #fff;
    }
    .ui-form input:focus,
    .ui-form select:focus {
        outline: 2px solid #b2f5ea;
        border-color: var(--brand);
    }
</style>
