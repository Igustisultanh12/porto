import { test, expect } from '@playwright/test';

test('admin publishes a project; SSR, filters, themes and mobile navigation work', async ({
    page,
    request,
}) => {
    const errors: string[] = [];
    page.on('pageerror', (error) => errors.push(error.message));
    page.on('console', (message) => {
        if (message.type() === 'error') errors.push(message.text());
    });
    await page.goto('/admin/login');
    await page.getByLabel('Email', { exact: true }).fill('admin@example.test');
    await page.getByLabel('Kata sandi', { exact: true }).fill(process.env.E2E_ADMIN_PASSWORD!);
    await page.getByRole('button', { name: 'Masuk →', exact: true }).click();
    await expect(page.getByRole('heading', { name: 'Selamat datang di Studio.' })).toBeVisible();
    await page.getByRole('link', { name: '+ Proyek', exact: true }).click();
    await page.getByLabel('Judul', { exact: true }).fill('[DEMO] Proyek pengujian browser');
    await page
        .getByLabel('Ringkasan', { exact: true })
        .fill('Konten sementara untuk membuktikan alur publikasi.');
    await page
        .getByLabel('Isi (Markdown)', { exact: true })
        .fill('## Hasil pengujian\n\nKonten ini dibuat otomatis oleh tes browser.');
    await page.getByLabel('Tampilkan di beranda').check();
    await page.getByRole('button', { name: 'Simpan konten', exact: true }).click();
    await expect(page.getByRole('status')).toContainText('Konten disimpan');
    const editUrl = page.url();
    const anonymous = await request.get('/karya/project/demo-proyek-pengujian-browser');
    expect(anonymous.status()).toBe(404);
    await page.getByLabel('Status situs').selectOption('published');
    await page.getByRole('button', { name: 'Simpan konten', exact: true }).click();
    await expect(page.getByRole('status')).toContainText('Perubahan disimpan');
    const html = await (await request.get('/karya/project/demo-proyek-pengujian-browser')).text();
    expect(html).toContain('<h1>');
    expect(html).toContain('Hasil pengujian</h2>');
    expect(html).toContain('property="og:title"');
    await page.goto('/');
    const jsonLd = await page.locator('script[type="application/ld+json"]').textContent();
    expect(JSON.parse(jsonLd || '{}')['@type']).toBe('WebPage');
    await expect(
        page.getByRole('link', { name: '[DEMO] Proyek pengujian browser', exact: true }),
    ).toBeVisible();
    await page.screenshot({
        path: 'test-results/home-desktop.png',
        fullPage: true,
        animations: 'disabled',
    });
    await page.getByRole('button', { name: 'Gunakan tema terang' }).click();
    await page.reload();
    await expect(page.locator('html')).toHaveClass('light');
    await page.getByRole('button', { name: 'Gunakan tema gelap' }).click();
    await page.setViewportSize({ width: 390, height: 844 });
    await page.screenshot({
        path: 'test-results/home-mobile.png',
        fullPage: true,
        animations: 'disabled',
    });
    expect(
        await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth),
    ).toBe(true);
    await page
        .getByRole('navigation', { name: 'Navigasi utama' })
        .getByRole('link', { name: 'Proyek', exact: true })
        .click();
    await page.getByLabel('Cari proyek').fill('pengujian');
    await page.getByRole('button', { name: 'Cari →', exact: true }).click();
    await expect(page.getByText('1 proyek ditemukan')).toBeVisible();
    await page.getByRole('link', { name: '[DEMO] Proyek pengujian browser', exact: true }).click();
    await expect(page.getByRole('heading', { name: 'Hasil pengujian' })).toBeVisible();
    await page.goto(editUrl);
    await expect(page.getByRole('heading', { name: 'Edit Proyek' })).toBeVisible();
    expect(
        await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth),
    ).toBe(true);
    await page.screenshot({
        path: 'test-results/admin-mobile.png',
        fullPage: true,
        animations: 'disabled',
    });
    await page.setViewportSize({ width: 1440, height: 1000 });
    await page.screenshot({
        path: 'test-results/admin-desktop.png',
        fullPage: true,
        animations: 'disabled',
    });
    await page.getByLabel('Status situs').selectOption('draft');
    await page.getByRole('button', { name: 'Simpan konten', exact: true }).click();
    await expect(page.getByRole('status')).toContainText('Perubahan disimpan');
    expect((await request.get('/karya/project/demo-proyek-pengujian-browser')).status()).toBe(404);
    expect(errors).toEqual([]);
});

test('public pages, contact and links work at mobile size', async ({ page, request }) => {
    const errors: string[] = [];
    page.on('pageerror', (e) => errors.push(e.message));
    expect((await request.post('/kontak', { data: {} })).status()).toBe(419);
    await page.setViewportSize({ width: 390, height: 844 });
    for (const url of [
        '/',
        '/tentang',
        '/karya/project',
        '/karya/research',
        '/karya/article',
        '/kontak',
        '/admin/login',
    ]) {
        const response = await page.goto(url);
        expect(response?.status()).toBe(200);
        expect(
            await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth),
        ).toBe(true);
        const urls = await page
            .locator('a[href^="/"]')
            .evaluateAll((elements) => [...new Set(elements.map((e) => e.getAttribute('href')!))]);
        for (const href of urls) {
            expect((await request.get(href)).status(), href).toBeLessThan(400);
        }
    }
    await page.goto('/kontak');
    await page.getByLabel('Nama', { exact: true }).fill('Penguji browser');
    await page.getByLabel('Email', { exact: true }).fill('tester@example.test');
    await page.getByLabel('Subjek', { exact: true }).fill('Pesan tes');
    await page
        .getByLabel('Pesan', { exact: true })
        .fill('Pesan ini menguji form kontak dari browser.');
    await page.getByRole('button', { name: 'Kirim pesan ↗' }).click();
    await expect(page.getByRole('status')).toContainText('Pesanmu sudah tersimpan');
    expect(errors).toEqual([]);
});
