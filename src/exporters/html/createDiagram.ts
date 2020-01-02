export function createDiagram(chord: string, code: (number | null)[]): string {

  const height = 100;
  const width = Math.round(height * 0.8 * 100) / 100;

  const min = code.reduce((acc: number, cur: number | null) => cur !== null ? Math.min(acc, cur) : acc, Infinity);
  const max = code.reduce((acc: number, cur: number | null) => cur !== null ? Math.max(acc, cur) : acc, 0);

  // add shift if higher chord than 4th fret
  let capo = 0;

  if (max > 4 && min > 1) {
    capo = min;
    code = code.map(tone => tone !== null ? tone - capo + 1 : null);
  }

  // find which strings can be player using barre
  let barreTone = code[5];
  let barreStrings = 1; // one string

  for (let string = 5; string >= 0; string--) {
    const tone = code[string];
    if (tone === null || tone === 0 || barreTone === null || tone < barreTone) break;

    if (tone === barreTone) {
      barreStrings = 5 - string + 1;
    }
  }

  let output = `
    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" height="${height}px" width="${width}px" viewBox="0 0 160 190">

      <!-- structure -->
      <line x1="8" y1="70" x2="112" y2="70" style="stroke:black;stroke-width:${capo ? 4 : 8}" />

      <line x1="10" y1="70" x2="10" y2="190" style="stroke:black;stroke-width:4" />
      <line x1="30" y1="70" x2="30" y2="190" style="stroke:black;stroke-width:4" />
      <line x1="50" y1="70" x2="50" y2="190" style="stroke:black;stroke-width:4" />
      <line x1="70" y1="70" x2="70" y2="190" style="stroke:black;stroke-width:4" />
      <line x1="90" y1="70" x2="90" y2="190" style="stroke:black;stroke-width:4" />
      <line x1="110" y1="70" x2="110" y2="190" style="stroke:black;stroke-width:4" />

      <line x1="10" y1="100" x2="110" y2="100" style="stroke:black;stroke-width:4" />
      <line x1="10" y1="130" x2="110" y2="130" style="stroke:black;stroke-width:4" />
      <line x1="10" y1="160" x2="110" y2="160" style="stroke:black;stroke-width:4" />
  `;

  if (capo) output += `<text x="120" y="97" fill="black" font-size="40" font-family="Calibri,sans-serif"> ${capo} </text>`;

  output += `<!-- dots -->`;

  code.forEach((tone, string) => {

    if (tone === null) {
      const x1 = 3 + string * 20;
      const x2 = 17 + string * 20;
      output += `<line x1="${x1}" y1="48" x2="${x2}" y2="62" style="stroke:black;stroke-width:2"/>`;
      output += `<line x1="${x1}" y1="62" x2="${x2}" y2="48" style="stroke:black;stroke-width:2"/>`;
    }
    else {
      const x = 10 + string * 20;
      const y = 55 + tone * 30;
      if (tone === 0) output += `<circle cx="${x}" cy="${y}" r="7" fill="transparent" stroke="black" stroke-width="2" />`;
      else output += `<circle cx="${x}" cy="${y}" r="7" fill="black"/>`;
    }
  })

  if (barreStrings > 1 && barreTone !== null) {
    output += `<!-- barré -->`;
    output += `<line x1="${10 + (5 - barreStrings + 1) * 20} " y1="${55 + barreTone * 30}" x2="110" y2="${55 + barreTone * 30}" style="stroke: black; stroke-width: 14; stroke-linecap: round;" />`;
  }

  output += `<!-- chord name -->`;
  output += `<text x="5" y="38" fill="black" font-size="40" font-family="dejavusans,sans-serif"> ${chord} </text>`;

  output += `</svg>`;

  return output;
}