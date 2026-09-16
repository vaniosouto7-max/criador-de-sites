import glob
import os

directory = os.path.dirname(os.path.abspath(__file__))
files = glob.glob(os.path.join(directory, "*.php"))

replacements = {
    'ÁƒÂ§ÁƒÂµes': 'ções',
    'ÁƒÂ§ÁƒÂ£o': 'ção',
    'ÁƒÂ§ÁƒÂ£': 'çã',
    'ÁƒÂ¡': 'á',
    'ÁƒÂ©': 'é',
    'ÁƒÂ­': 'í',
    'ÁƒÂ³': 'ó',
    'ÁƒÂº': 'ú',
    'ÁƒÂ¢': 'â',
    'ÁƒÂª': 'ê',
    'ÁƒÂ´': 'ô',
    'ÁƒÂ£': 'ã',
    'ÁƒÂµ': 'õ',
    'ÁƒÂ§': 'ç',
    'Áƒâ€¡ÁƒÆ’O': 'ÇÃO',
    'Áƒâ€¡Áƒâ€¢ES': 'ÇÕES',
    'Áƒâ€¡': 'Ç',
    'Áƒâ€š': 'Â',
    'Ã§Ã£o': 'ção',
    'Ã§Ãµes': 'ções',
    'Ã¡': 'á',
    'Ã©': 'é',
    'Ã­': 'í',
    'Ã³': 'ó',
    'Ãº': 'ú',
    'Ã£': 'ã',
    'Ãµ': 'õ',
    'Ã§': 'ç',
    'Ã¢': 'â',
    'Ãª': 'ê',
    'Ã´': 'ô',
    'Ãš': 'Ú'
}

for file in files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    original_content = content
    for search, replace in replacements.items():
        content = content.replace(search, replace)
        
    if content != original_content:
        with open(file, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Fixed {os.path.basename(file)}")

print("Done")
