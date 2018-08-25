using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Server.Models
{
    public class Album
    {
        public int ID { get; set; }
        public string Name { get; set; }
        public int Artist { get; set; }
        public string AlbumArt { get; set; }

        public DateTime Release { get; set; }

        public string Wiki_Page { get; set; }
        public string LastFm_Page { get; set; }
        public string Amazon_Page { get; set; }
    }
}
