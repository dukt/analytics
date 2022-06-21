const {test, expect} = require('@playwright/test');

test('Shoud show Analytics widgets in the Dashboard’s new widget menu', async ({ page, context, baseURL }) => {
  await page.goto(baseURL + '/dashboard');
  const title = page.locator('h1');
  await expect(title).toHaveText('Dashboard');

  const handle = await page.$('#newwidgetmenubtn');

  let menuId = (await handle.getAttribute('aria-controls')).replace('.', '\\.')

  await handle.click()
  await page.waitForSelector(`#${menuId}`)

  expect((await page.locator('ul#' + menuId + ' li a[data-type="dukt\\\\analytics\\\\widgets\\\\Realtime"]:has-text("Active users")').count() > 0)).toBeTruthy();
  expect((await page.locator('ul#' + menuId + ' li a[data-type="dukt\\\\analytics\\\\widgets\\\\Report"]:has-text("Analytics Report")').count() > 0)).toBeTruthy();
  expect((await page.locator('ul#' + menuId + ' li a[data-type="dukt\\\\analytics\\\\widgets\\\\Ecommerce"]:has-text("E-commerce")').count() > 0)).toBeTruthy();
});
