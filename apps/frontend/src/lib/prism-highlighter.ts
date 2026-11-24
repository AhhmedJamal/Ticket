import Prism from 'prismjs';

// Base markup & clike
import 'prismjs/components/prism-markup';
import 'prismjs/components/prism-markup-templating';
import 'prismjs/components/prism-clike';

// Languages
import 'prismjs/components/prism-javascript';
import 'prismjs/components/prism-typescript';
import 'prismjs/components/prism-php';
import 'prismjs/components/prism-python';
import 'prismjs/components/prism-sql';
import 'prismjs/components/prism-json';
import 'prismjs/components/prism-bash';
import 'prismjs/components/prism-css';

// Enrich JavaScript & TypeScript to distinguish variables from keywords and functions
if (Prism.languages.javascript) {
  Prism.languages.insertBefore('javascript', 'keyword', {
    'variable-declaration': {
      pattern: /\b(?:const|let|var)\s+[a-zA-Z_$][a-zA-Z0-9_$]*/i,
      inside: {
        'keyword': /\b(?:const|let|var)\b/,
        'variable': /[a-zA-Z_$][a-zA-Z0-9_$]*/,
      },
    },
    'variable-assignment': {
      pattern: /\b[a-zA-Z_$][a-zA-Z0-9_$]*(?=\s*(?:=|\+=|-=|\*=|\/=|%=|&=|\|=|\^=)(?!=))/i,
      alias: 'variable',
    },
  });
}

if (Prism.languages.typescript) {
  Prism.languages.insertBefore('typescript', 'keyword', {
    'variable-declaration': {
      pattern: /\b(?:const|let|var)\s+[a-zA-Z_$][a-zA-Z0-9_$]*/i,
      inside: {
        'keyword': /\b(?:const|let|var)\b/,
        'variable': /[a-zA-Z_$][a-zA-Z0-9_$]*/,
      },
    },
    'variable-assignment': {
      pattern: /(?<!:\s*)\b[a-zA-Z_$][a-zA-Z0-9_$]*(?=\s*(?:=|\+=|-=|\*=|\/=|%=|&=|\|=|\^=)(?!=))/i,
      alias: 'variable',
    },
  });
}

if (Prism.languages.python) {
  Prism.languages.insertBefore('python', 'keyword', {
    'variable-assignment': {
      pattern: /\b[a-zA-Z_][a-zA-Z0-9_]*(?=\s*(?:=|\+=|-=|\*=|\/=|:=)(?!=))/i,
      alias: 'variable',
    },
  });
}

export function highlightCode(code: string, language: string): string {
  if (!code) return '';
  const langKey = language.toLowerCase();

  let grammar = Prism.languages[langKey];
  if (!grammar) {
    if (langKey === 'js') grammar = Prism.languages.javascript;
    else if (langKey === 'ts') grammar = Prism.languages.typescript;
    else if (langKey === 'py') grammar = Prism.languages.python;
    else if (langKey === 'html' || langKey === 'xml') grammar = Prism.languages.markup;
    else if (langKey === 'sh' || langKey === 'shell') grammar = Prism.languages.bash;
    else grammar = Prism.languages.plaintext || Prism.languages.clike;
  }

  try {
    return Prism.highlight(code, grammar || Prism.languages.clike, langKey);
  } catch {
    return code
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }
}
