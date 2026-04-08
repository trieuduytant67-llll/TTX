<?php
// Đường dẫn folder chứa PDF
$dir = __DIR__ . '/pdf/';
// Lấy danh sách file PDF
$pdfFiles = glob($dir . '*.pdf');
sort($pdfFiles);
if (empty($pdfFiles)) {
    die("Không tìm thấy file PDF nào trong thư mục 'pdf'.");
}
// Chuyển danh sách file thành mảng tên file (không đường dẫn)
$pdfFileNames = array_map('basename', $pdfFiles);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>SƠ ĐỒ THI</title>


    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta property="og:title" content="TS THPT chuyên KHTN">
        <meta property="og:description" content="Tuyển sinh THPT chuyên Khoa học Tự nhiên">
        <meta property="og:image" content="./image/hus_logo.webp">
        <meta property="og:url" content="https://tschuyenkhtn.hus.vnu.edu.vn">
        <title>TS THPT chuyên KHTN</title>
        <link rel="icon" type="image/webp" href="image/hus_logo.webp">

        <style>
            body,
            html {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                background: #f5f5f5;
            }

            h1 {
                /* background: #222; */
                color: rgb(0, 64, 128);
                padding: 10px;
                text-align: center;
                margin: 0 0 20px 0;
            }

            .pdf-container {
                width: 90vw;
                max-width: 900px;
                margin: 0 auto 40px auto;
                background: white;
                box-shadow: 0 0 8px rgba(0, 0, 0, 0.15);
                padding: 10px;
            }

            .pdf-title {
                text-align: center;
                margin: 10px 0;
                font-weight: bold;
            }

            canvas {
                display: block;
                margin: 10px auto;
                max-width: 100%;
                height: auto !important;
                border: 1px solid #ccc;
            }
        </style>
    </head>

<body>

    <h1>TUYỂN SINH THPT CHUYÊN KHOA HỌC TỰ NHIÊN NĂM 2025</h1>

    <div id="pdf-viewers"></div>

    <!-- PDF.js từ CDN Mozilla -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.9.179/pdf.min.js"></script>

    <script>
        // Danh sách file PDF lấy từ PHP
        const pdfFiles = <?php echo json_encode($pdfFileNames); ?>;

        // Thư mục chứa PDF
        const pdfFolder = 'pdf/';

        // Cấu hình PDF.js worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.9.179/pdf.worker.min.js';

        // Container để chèn PDF viewer
        const container = document.getElementById('pdf-viewers');

        // Hàm render 1 trang PDF ra canvas
        async function renderPage(pdfDoc, pageNum, canvas) {
            const page = await pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({
                scale: 1.5
            }); // tỉ lệ zoom 1.5
            const context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: context,
                viewport: viewport
            };
            await page.render(renderContext).promise;
        }

        // Hàm render 1 file PDF đầy đủ
        async function renderPDF(fileName) {
            // Container con chứa 1 PDF
            const pdfDiv = document.createElement('div');
            pdfDiv.className = 'pdf-container';

            // Tiêu đề file
            const title = document.createElement('div');
            title.className = 'pdf-title';
            // title.textContent = fileName;   // Hiển thị tên file
            pdfDiv.appendChild(title);

            // Đường dẫn file PDF
            const url = pdfFolder + encodeURIComponent(fileName);

            try {
                const pdfDoc = await pdfjsLib.getDocument(url).promise;
                // Lặp từng trang
                for (let i = 1; i <= pdfDoc.numPages; i++) {
                    const canvas = document.createElement('canvas');
                    pdfDiv.appendChild(canvas);
                    await renderPage(pdfDoc, i, canvas);
                }
            } catch (error) {
                const errorMsg = document.createElement('div');
                errorMsg.style.color = 'red';
                errorMsg.textContent = 'Lỗi tải file PDF: ' + error.message;
                pdfDiv.appendChild(errorMsg);
            }
            container.appendChild(pdfDiv);
        }

        // Render tất cả file PDF theo thứ tự
        (async function() {
            for (const fileName of pdfFiles) {
                await renderPDF(fileName);
            }
        })();
    </script>

</body>

</html>