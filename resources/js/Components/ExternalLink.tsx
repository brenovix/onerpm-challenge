import { ExternalLink } from "lucide-react";


const ExternalURL: React.FC<{ url: string }> = ({ url }) => {
  const handleClick = (e: React.MouseEvent<HTMLAnchorElement>) => {
    e.preventDefault();
    window.open(url, '_blank', 'noopener,noreferrer');
  };

  return (
    <a href={url} onClick={handleClick} className="text-blue-500 hover:underline">
      <ExternalLink className="w-5 h-5" />
    </a>
  );
}

export default ExternalURL;