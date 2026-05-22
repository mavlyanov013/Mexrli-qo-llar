const DIGRAPHS = [
    ['Sh', 'Ш'],
    ['SH', 'Ш'],
    ['sh', 'ш'],
    ['Ch', 'Ч'],
    ['CH', 'Ч'],
    ['ch', 'ч'],
    ["Oʻ", 'Ў'],
    ["O'", 'Ў'],
    ['O‘', 'Ў'],
    ["oʻ", 'ў'],
    ["o'", 'ў'],
    ['o‘', 'ў'],
    ['ʻ', 'ў'],
    ['ʼ', 'ў'],
    ['`', 'ъ'],
    ["Gʻ", 'Ғ'],
    ["G'", 'Ғ'],
    ['G‘', 'Ғ'],
    ["gʻ", 'ғ'],
    ["g'", 'ғ'],
    ['g‘', 'ғ'],
    ['Ng', 'Нг'],
    ['ng', 'нг'],
]

const CHARS = {
    A: 'А', a: 'а',
    B: 'Б', b: 'б',
    D: 'Д', d: 'д',
    E: 'Е', e: 'е',
    F: 'Ф', f: 'ф',
    G: 'Г', g: 'г',
    H: 'Ҳ', h: 'ҳ',
    I: 'И', i: 'и',
    J: 'Ж', j: 'ж',
    K: 'К', k: 'к',
    L: 'Л', l: 'л',
    M: 'М', m: 'м',
    N: 'Н', n: 'н',
    O: 'О', o: 'о',
    P: 'П', p: 'п',
    Q: 'Қ', q: 'қ',
    R: 'Р', r: 'р',
    S: 'С', s: 'с',
    T: 'Т', t: 'т',
    U: 'У', u: 'у',
    V: 'В', v: 'в',
    X: 'Х', x: 'х',
    Y: 'Й', y: 'й',
    Z: 'З', z: 'з',
    "'": 'ъ',
}

const I18N_PLACEHOLDER_RE = /(\{[^{}]+\})/g

function transliterateSegment(text) {
    let result = text

    DIGRAPHS.forEach(([from, to]) => {
        result = result.split(from).join(to)
    })

    return result
        .split('')
        .map((char) => CHARS[char] ?? char)
        .join('')
}

export function transliterateLatinToCyrillic(text) {
    if (!text || typeof text !== 'string') return text

    return text
        .split(I18N_PLACEHOLDER_RE)
        .map((part) => (part.startsWith('{') && part.endsWith('}') ? part : transliterateSegment(part)))
        .join('')
}

export function transliterateDeep(value) {
    if (typeof value === 'string') {
        return transliterateLatinToCyrillic(value)
    }

    if (Array.isArray(value)) {
        return value.map((item) => transliterateDeep(item))
    }

    if (value && typeof value === 'object') {
        return Object.fromEntries(
            Object.entries(value).map(([key, item]) => [key, transliterateDeep(item)])
        )
    }

    return value
}
