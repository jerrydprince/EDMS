import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import fr from './locales/fr.json';
import es from './locales/es.json';
import de from './locales/de.json';
import zh from './locales/zh.json';
import it from './locales/it.json';

const messages = {
  en,
  fr,
  es,
  de,
  zh,
  it
};

// We will export a function to create i18n with the initial locale
export function setupI18n(userLanguage) {
  const languageMap = {
    'English': 'en',
    'French': 'fr',
    'Spanish': 'es',
    'German': 'de',
    'Chinese': 'zh',
    'Italian': 'it'
  };

  const locale = languageMap[userLanguage] || 'en';

  return createI18n({
    locale: locale,
    fallbackLocale: 'en',
    messages,
    legacy: false, // For Vue 3 Composition API support
  });
}
