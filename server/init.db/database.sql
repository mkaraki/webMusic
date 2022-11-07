CREATE TABLE IF NOT EXISTS user(
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(256) NOT NULL UNIQUE,
    passhash TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS sessionToken(
    token CHAR(64) PRIMARY KEY,
    userid INT NOT NULL,
    expire DATETIME NOT NULL,
    FOREIGN KEY (userid) REFERENCES user(id)
);
CREATE TABLE IF NOT EXISTS library(
    id INT PRIMARY KEY AUTO_INCREMENT,
    basepath TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS accessList(
    id INT PRIMARY KEY AUTO_INCREMENT,
    userid INT NOT NULL,
    libraryId INT NOT NULL,
    permission INT DEFAULT(5),
    FOREIGN KEY(libraryId) REFERENCES library(id),
    FOREIGN KEY (userid) REFERENCES user(id)
);
CREATE TABLE IF NOT EXISTS artistMetadata(
    mbid CHAR(36) PRIMARY KEY,
    name TEXT,
    namePhonic TEXT,
    nameAlphabet TEXT,
    imagePath TEXT,
    disambiguation LONGTEXT
);
CREATE TABLE IF NOT EXISTS releaseMetadata(
    mbid CHAR(36) PRIMARY KEY,
    title TEXT,
    titlePhonic TEXT,
    artworkPath TEXT,
    artworkColor VARCHAR(6) DEFAULT(NULL),
    releaseDate DATE,
    disambiguation LONGTEXT
);
CREATE TABLE IF NOT EXISTS trackMetadata(
    trackMbid CHAR(36) PRIMARY KEY,
    recordingMbid CHAR(36),
    releaseMbid CHAR(36),
    title TEXT,
    duration INT,
    diskNo INT NOT NULL DEFAULT(0),
    trackNo INT NOT NULL DEFAULT(0),
    FOREIGN KEY(releaseMbid) REFERENCES releaseMetadata(mbid)
);
CREATE TABLE IF NOT EXISTS track(
    id INT PRIMARY KEY AUTO_INCREMENT,
    libraryId INT NOT NULL,
    trackMbid CHAR(36) NOT NULL,
    path TEXT NOT NULL,
    FOREIGN KEY(libraryId) REFERENCES library(id),
    FOREIGN KEY(trackMbid) REFERENCES trackMetadata(trackMbid)
);
CREATE TABLE IF NOT EXISTS artistMap(
    id INT PRIMARY KEY AUTO_INCREMENT,
    artistMbid CHAR(36) NOT NULL,
    mapNo INT DEFAULT(0),
    dispName TEXT DEFAULT(NULL),
    joinPhrase TEXT DEFAULT(NULL),
    releaseMbid CHAR(36) DEFAULT(NULL),
    trackMbid CHAR(36) DEFAULT(NULL),
    FOREIGN KEY(artistMbid) REFERENCES artistMetadata(mbid),
    FOREIGN KEY(releaseMbid) REFERENCES releaseMetadata(mbid),
    FOREIGN KEY(trackMbid) REFERENCES trackMetadata(trackMbid)
);