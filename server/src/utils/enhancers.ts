import { load } from 'cheerio';

export function removeConsecutiveSpecialCharacters(input: string): string {
  return input.replace(/([\-_*#]{2,})/g, (m) => m[0]);
}

export function stripParenthesesWrappingContactTokens(input: string): string {
  return input.replace(/\((\s*(?:call|contact|click|visit)[^)]*)\)/gi, '$1');
}

export function stripParenthesesAroundAnchorTags(input: string): string {
  return input.replace(/\((\s*<a [^>]+>[^<]*<\/a>\s*)\)/gi, '$1');
}

export function removePTags(input: string): string {
  return input.replace(/<p>(\s|&nbsp;)*<\/p>/g, '').replace(/\n/g, '<br>');
}

export function convertEmailsToLinks(input: string): string {
  return input.replace(
    /([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/g,
    '<a href="mailto:$1">$1</a>'
  );
}

export function convertUrlsToLinks(input: string): string {
  return input.replace(
    /(?<![\"'=])(https?:\/\/[\w.-]+(?:\/[\w\-._~:/?#[\]@!$&'()*+,;=.]+)?)/g,
    '<a href="$1" target="_blank" rel="nofollow noopener">$1</a>'
  );
}

export function generateAnchorId(text: string) {
  return text
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-');
}

export function generateTOCFromContent(html: string) {
  const $ = load(html);
  const items: { id: string; text: string }[] = [];
  $('h2, h3').each((_: any, el: any) => {
    const text = $(el).text();
    const id = generateAnchorId(text);
    $(el).attr('id', id);
    items.push({ id, text });
  });
  if (!items.length) return html;
  const tocList = items.map((i) => `<li><a href="#${i.id}">${i.text}</a></li>`).join('');
  const tocHtml = `<nav class="toc"><ul>${tocList}</ul></nav>`;
  $('body').prepend(tocHtml);
  return $.html();
}

export function verifyAndFixTOCLinks(html: string) {
  const $ = load(html);
  $('nav.toc a[href^="#"]').each((_: any, a: any) => {
    const $a = $(a);
    const href = $a.attr('href');
    if (!href) return;
    const id = href.replace(/^#/, '');
    if (!$(`#${id}`).length) {
      const text = $a.text();
      const fixed = generateAnchorId(text);
      $a.attr('href', `#${fixed}`);
    }
  });
  return $.html();
}

export function cleanupHtml(input: string) {
  let out = input || '';
  out = removeConsecutiveSpecialCharacters(out);
  out = stripParenthesesWrappingContactTokens(out);
  out = stripParenthesesAroundAnchorTags(out);
  out = removePTags(out);
  out = convertEmailsToLinks(out);
  out = convertUrlsToLinks(out);
  return out;
}

export function enhanceWithTOC(html: string) {
  return generateTOCFromContent(html);
}

export function fixAnchors(html: string) {
  return verifyAndFixTOCLinks(html);
}
