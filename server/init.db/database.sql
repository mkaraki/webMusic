CREATE TABLE IF NOT EXISTS user(
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(256) NOT NULL UNIQUE,
    passhash TEXT NOT NULL,
    permission INT NOT NULL DEFAULT(1),
    listenBrainzKey CHAR(36) DEFAULT(NULL)
);
CREATE TABLE IF NOT EXISTS sessionToken(
    token CHAR(64) PRIMARY KEY,
    userid INT NOT NULL,
    expire DATETIME NOT NULL,
    FOREIGN KEY (userid) REFERENCES user(id)
);
CREATE TABLE IF NOT EXISTS library(
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(256),
    basepath TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS accessList(
    id INT PRIMARY KEY AUTO_INCREMENT,
    userid INT,
    libraryId INT NOT NULL,
    permission INT DEFAULT(5),
    FOREIGN KEY(libraryId) REFERENCES library(id),
    FOREIGN KEY (userid) REFERENCES user(id)
);
CREATE TABLE IF NOT EXISTS artistMetadata(
    id INT PRIMARY KEY AUTO_INCREMENT,
    mbid CHAR(36),
    name TEXT,
    namePhonic TEXT,
    nameAlphabet TEXT,
    imagePath TEXT,
    disambiguation LONGTEXT
);
CREATE TABLE IF NOT EXISTS releaseMetadata(
    id INT PRIMARY KEY AUTO_INCREMENT,
    libraryId INT NOT NULL,
    releaseMbid CHAR(36),
    title TEXT,
    titlePhonic TEXT,
    artworkPath TEXT,
    releaseDate DATE,
    disambiguation LONGTEXT,
    FOREIGN KEY(libraryId) REFERENCES library(id)
);
CREATE TABLE IF NOT EXISTS track(
    id INT PRIMARY KEY AUTO_INCREMENT,
    libraryId INT NOT NULL,
    recordingMbid CHAR(36),
    trackMbid CHAR(36),
    releaseId INT,
    title TEXT,
    duration INT,
    diskNo INT NOT NULL DEFAULT(0),
    trackNo INT NOT NULL DEFAULT(0),
    path TEXT NOT NULL,
    FOREIGN KEY(libraryId) REFERENCES library(id),
    FOREIGN KEY (releaseId) REFERENCES releaseMetadata(id)
);
CREATE TABLE IF NOT EXISTS artistMap(
    id INT PRIMARY KEY AUTO_INCREMENT,
    type INT NOT NULL,
    artistId INT NOT NULL,
    mapNo INT DEFAULT(0),
    dispName TEXT NOT NULL,
    joinPhrase TEXT DEFAULT(''),
    releaseId INT DEFAULT(NULL),
    trackId INT DEFAULT(NULL),
    FOREIGN KEY(artistId) REFERENCES artistMetadata(id),
    FOREIGN KEY(releaseId) REFERENCES releaseMetadata(id),
    FOREIGN KEY(trackId) REFERENCES track(id)
);
CREATE TABLE IF NOT EXISTS dbInfo(id INT PRIMARY KEY, info TEXT NOT NULL);
INSERT INTO dbInfo
VALUES (0, '2022112400001');