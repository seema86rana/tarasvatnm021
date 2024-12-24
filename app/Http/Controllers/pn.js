const puppeteer = require('puppeteer');

async function generatePDF(htmlContent) {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    await page.setContent(htmlContent);
    await page.pdf({ path: 'exampler.pdf', format: 'A4' });
    await browser.close();
}

const htmlContent = process.argv[2]; // Pass HTML content via arguments
generatePDF(htmlContent);
