import pypdf
import os

pdf_path = "DOCUMENTAO_PROJETO_FINAL_ARQDESBD__ANDRESSA_E_MATHEUS.pdf"
reader = pypdf.PdfReader(pdf_path)
page = reader.pages[0]

os.makedirs("scratch", exist_ok=True)
count = 0
for image_file_object in page.images:
    with open(f"scratch/logo_{count}.png", "wb") as fp:
        fp.write(image_file_object.data)
    count += 1

print(f"Extracted {count} images from page 1.")
