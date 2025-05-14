import { useState, useEffect, useRef } from 'react';
import { CKEditor } from '@ckeditor/ckeditor5-react';

import {
  ClassicEditor,
  AccessibilityHelp,
  Autoformat,
  Autosave,
  Bold,
  Code,
  Essentials,
  FindAndReplace,
  FontBackgroundColor,
  FontColor,
  FontFamily,
  FontSize,
  GeneralHtmlSupport,
  Highlight,
  Italic,
  Paragraph,
  RemoveFormat,
  SelectAll,
  SpecialCharacters,
  SpecialCharactersArrows,
  SpecialCharactersCurrency,
  SpecialCharactersEssentials,
  SpecialCharactersLatin,
  SpecialCharactersMathematical,
  SpecialCharactersText,
  Strikethrough,
  Subscript,
  Superscript,
  TextPartLanguage,
  TextTransformation,
  Title,
  Underline,
  Undo
} from 'ckeditor5';

import 'ckeditor5/ckeditor5.css';

import './App.css';

export default function App() {
  const editorContainerRef = useRef(null);
  const editorRef = useRef(null);
  const [isLayoutReady, setIsLayoutReady] = useState(false);

  useEffect(() => {
    setIsLayoutReady(true);

    return () => setIsLayoutReady(false);
  }, []);

  const editorConfig = {
    toolbar: {
      items: [
        'undo',
        'redo',
        '|',
        'findAndReplace',
        'selectAll',
        'textPartLanguage',
        '|',
        'fontSize',
        'fontFamily',
        'fontColor',
        'fontBackgroundColor',
        '|',
        'bold',
        'italic',
        'underline',
        'strikethrough',
        'subscript',
        'superscript',
        'code',
        'removeFormat',
        '|',
        'specialCharacters',
        'highlight',
        '|',
        'accessibilityHelp'
      ],
      shouldNotGroupWhenFull: false
    },
    plugins: [
      AccessibilityHelp,
      Autoformat,
      Autosave,
      Bold,
      Code,
      Essentials,
      FindAndReplace,
      FontBackgroundColor,
      FontColor,
      FontFamily,
      FontSize,
      GeneralHtmlSupport,
      Highlight,
      Italic,
      Paragraph,
      RemoveFormat,
      SelectAll,
      SpecialCharacters,
      SpecialCharactersArrows,
      SpecialCharactersCurrency,
      SpecialCharactersEssentials,
      SpecialCharactersLatin,
      SpecialCharactersMathematical,
      SpecialCharactersText,
      Strikethrough,
      Subscript,
      Superscript,
      TextPartLanguage,
      TextTransformation,
      Title,
      Underline,
      Undo
    ],
    fontFamily: {
      supportAllValues: true
    },
    fontSize: {
      options: [10, 12, 14, 'default', 18, 20, 22],
      supportAllValues: true
    },
    htmlSupport: {
      allow: [
        {
          name: /^.*$/,
          styles: true,
          attributes: true,
          classes: true
        }
      ]
    },
    initialData:
      "",
    placeholder: 'Pertanyaan'
  };

  return (
    <div>
      <div className="main-container">
        <div className="editor-container editor-container_classic-editor" ref={editorContainerRef}>
          <div className="editor-container__editor">
            <div ref={editorRef}>{isLayoutReady && <CKEditor editor={ClassicEditor} config={editorConfig} />}</div>
          </div>
        </div>
      </div>
    </div>
  );
}
