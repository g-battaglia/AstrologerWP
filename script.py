import re
import sys
import os

def fix_unclosed_esc_html(file_path):
    # Regex per trovare i casi errati
    pattern = re.compile(r'(<?php\s+echo\s+esc_html\(\s*(\$[a-zA-Z_][a-zA-Z0-9_]*)\s*);\s*\?>')
    replacement = r'<?php echo esc_html(\1); ?>'

    # Legge il file
    with open(file_path, 'r', encoding='utf-8') as file:
        content = file.read()

    # Sostituisce i match trovati
    fixed_content = pattern.sub(replacement, content)

    # Scrive il file corretto
    with open(file_path, 'w', encoding='utf-8') as file:
        file.write(fixed_content)

    print(f"Corretto: {file_path}")

def fix_files_in_directory(directory):
    for root, _, files in os.walk(directory):
        for file in files:
            if file.endswith(".php"):
                fix_unclosed_esc_html(os.path.join(root, file))

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Uso: python fix_unclosed_esc_html.py <directory>")
        sys.exit(1)

    fix_files_in_directory(sys.argv[1])
