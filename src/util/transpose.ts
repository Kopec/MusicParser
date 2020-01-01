export function index2tone(index: number): string {
  return (new Array("C", "C#", "D", "D#", "E", "F", "F#", "G", "G#", "A", "A#", "B"))[((index % 12) + 12) % 12];
}

export function tone2index(tone: string): number | undefined {
  const chordSeries = "c.d.ef.g.a.b...........h";

  let chordIndex = chordSeries.indexOf(tone.substr(0, 1).toLowerCase());
  if (chordIndex === -1) return undefined; // tone is not found

  if (tone.substr(1).match(/^is|#|♯/)) chordIndex++;
  if (tone.substr(1).match(/^es|s|b|♭/)) chordIndex--;

  chordIndex = chordIndex % 12;

  return chordIndex;

}