

type Artist = {
  id: number;
  name: string;
}

type Album = {
  id: number;
  title: string;
  artists: Artist[];
  cover: string;
  releaseDarte: string;
  externalUrl: string;
}

type Track = {
  id: number;
  isrc: string;
  title: string;
  artists: Artist[];
  album: Album;
  duration: number;
  externalUrl: string;
  brEnabled: boolean;
  previewUrl: string;
}
