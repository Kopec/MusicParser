import { Chord, Song, Diagram, Tab, DebugExporter, Text } from "../src"
import { DEFAULT_ENCODING } from "crypto";

describe("Transposition of song tune and individual chords.", () => {

  test("Song transposition by difference", () => {

    const song = new Song(`[C]
    -->
    3. Third [D]verse...
    
    [[C x32010]]
    
    e|---1--2--
    `);

    song.transpose(2);

    const chords = (<Chord[]>song.getAllChildren().filter(child => child instanceof Chord)).map(chord => chord.chord);

    const diagram = <Diagram>song.getAllChildren().filter(child => child instanceof Diagram)[0];
    const tab = <Tab>song.getAllChildren().filter(child => child instanceof Tab)[0];

    expect(chords).toEqual(["D", "E"]);

    expect(diagram.chord).toBe("D");
    expect(diagram.code).toBe("x54232");

    expect(tab.tab).toBe("e|---3--4--");
  });

  test("Get song tune", () => {

    const song = new Song(`[C#]
    -->
    3. Third [D]verse...
    
    [[C x32010]]
    
    e|---1--2--
    `);

    expect(song.getTune()).toBe("C♯");
  });

  test("Song transposition by target tune", () => {

    const song = new Song(`[C]
    -->
    3. Third [D]verse...
    
    [[C x32010]]
    
    e|---1--2--
    `);

    song.setTune("D");

    const chords = (<Chord[]>song.getAllChildren().filter(child => child instanceof Chord)).map(chord => chord.chord);

    const diagram = <Diagram>song.getAllChildren().filter(child => child instanceof Diagram)[0];
    const tab = <Tab>song.getAllChildren().filter(child => child instanceof Tab)[0];

    expect(chords).toEqual(["D", "E"]);

    expect(diagram.chord).toBe("D");
    expect(diagram.code).toBe("x54232");

    expect(tab.tab).toBe("e|---3--4--");
  })

  test("Diagram transposition into negative", () => {
    const chord = new Diagram("[[C x32010]]");

    chord.transpose(-1);

    expect(chord.chord).toBe("B");
    expect(chord.code).toBe("xx1413111211");
  });
})