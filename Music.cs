using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Server.Models
{
    public class Music
    {
        public int ID { get; set; }
        public string Name { get; set; }
        public string Path { get; set; }
        public DateTime Release { get; set; }
        public int Album { get; set; }
        public int Artist { get; set; }

        public int Lyric { get; set; }
    }
}
