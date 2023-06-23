import { test, expect } from '@playwright/test';
import * as dotenv from "dotenv";

dotenv.config({ path: __dirname + '/../../.env' });

const url = process.env.BASE_URL;

test('Cadastro de atleta', async ({ page }) => {
    await page.goto(url);
    await page.getByRole('link', { name: 'Já sou cadastrado' }).click();
    await page.getByRole('textbox').click();
    await page.getByRole('textbox').fill('mateuxlucax@gmail.com');
    await page.getByRole('button', { name: 'Continuar' }).click();
    await page.getByLabel('Senha').click();
    await page.getByLabel('Senha').fill('12345678');
    await page.getByRole('button', { name: 'Entrar' }).click();
    await page.getByRole('link', { name: ' Meus atletas' }).click();
    await page.getByRole('link', { name: ' Cadastrar atleta' }).click();
    await page.getByLabel('Nome completo').click();
    await page.getByLabel('Nome completo').fill('Atleta Teste 02');
    await page.getByRole('combobox', { name: 'Sexo' }).selectOption('F');
    await page.getByLabel('Data de nascimento').fill('2002-02-02');
    await page.getByLabel('Observações').click();
    await page.getByLabel('Observações').fill('Exemplo de observação de teste');
    await page.getByRole('button', { name: ' Cadastrar' }).click();
    await expect(page).toHaveURL(`${url}/tecnico/atletas/`);
    const html = await page.innerHTML('body');
    expect(html).toContain('Atleta Teste 02');
    const browser = await page.context().browser();
    await page.screenshot({ path: `./tests/screenshots/cadastroDeAtleta-${browser.browserType().name()}.png` });
});