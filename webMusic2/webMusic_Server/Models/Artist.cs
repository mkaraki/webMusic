using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading.Tasks;

namespace webMusic_Server.Models
{
    public class Artist
    {
        public int ID { get; set; }
        public string Name { get; set; }

        public DateTime Born { get; set; }
        public DateTime Died { get; set; }

        public string Wiki_Page { get; set; }
        public string LastFm_Page { get; set; }
    }
}
