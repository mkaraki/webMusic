using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace Server.Models
{
    public class Lyric
    {
        public int ID { get; set; }
        public bool Online { get; set; }
        public string Source { get; set; }
        public string URL { get; set; }
    }
}
