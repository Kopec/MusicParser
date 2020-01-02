export function normalizeChord(chord: string): string {
  return [
    
    chord.substr(0, 1).toUpperCase(),
    
    chord.substr(1).replace(/^es|s|b/, "♭").replace(/^is|#/, "♯")
    
  ].join("");
}