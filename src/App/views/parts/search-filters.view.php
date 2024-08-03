<section class="filtro-group">
    <p for="zona">Zona</p>
    <input type="text" placeholder="Luján..." name="zona" id="zona">
</section>

<section class="filtro-group">
    <p for="precio">Precio</p>
    <input type="range" id="precio" name="precio" min="0" max="1000000" step="5000" value="0" oninput="actualizarPrecio(this.value)">
    <h3 id="precio-valor">-</h3>
</section>

<section class="filtro-group">
    <p>Tipo</p>
    <label><input type="radio" name="tipo" value="casa" <?= ($tipo === 'casa') ? 'checked' : ''; ?>> Casa</label>
    <label><input type="radio" name="tipo" value="departamento" <?= ($tipo === 'departamento') ? 'checked' : ''; ?>> Departamento</label>
    <label><input type="radio" name="tipo" value="quinta" <?= ($tipo === 'quinta') ? 'checked' : ''; ?>> Quinta</label></section>


<section class="filtro-group">
    <p>Instalaciones & Equipamiento</p>
    <label><input type="checkbox" name="instalaciones[]" value="cochera" <?= in_array('cochera', $instalaciones) ? 'checked' : ''; ?>> Cochera</label>
    <label><input type="checkbox" name="instalaciones[]" value="pileta" <?= in_array('pileta', $instalaciones) ? 'checked' : ''; ?>> Pileta</label>
    <label><input type="checkbox" name="instalaciones[]" value="aire_acondicionado" <?= in_array('aire_acondicionado', $instalaciones) ? 'checked' : ''; ?>> Aire Acondicionado</label>
    <label><input type="checkbox" name="instalaciones[]" value="wifi" <?= in_array('wifi', $instalaciones) ? 'checked' : ''; ?>> Wi-Fi</label></section>

<section class="botones-conteiner">
    <button type="submit">Aplicar</button>
    <button type="reset">Limpiar</button>
</section>