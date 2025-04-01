
-- Students Table: Stores student voter information
CREATE TABLE Students (
    studentID INT PRIMARY KEY AUTO_INCREMENT,  
    name VARCHAR(100) NOT NULL,  
    email VARCHAR(100) UNIQUE,  
    password CHAR(60) NOT NULL,  
    dateOfBirth DATE NOT NULL,  
    department VARCHAR(100) NOT NULL,  
    contactNumber VARCHAR(15),  
    registrationDate DATE NOT NULL DEFAULT current_timestamp,  
    status ENUM('Active', 'Inactive') DEFAULT 'Active',  
    type TINYINT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Candidates Table: Stores election candidates
CREATE TABLE Candidates (
    candidateID INT PRIMARY KEY AUTO_INCREMENT,
    studentID INT,  
    position VARCHAR(100) NOT NULL,  
    manifesto TEXT, 
    photo VARCHAR(255),
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending', 
    FOREIGN KEY (studentID) REFERENCES Students(studentID) ON DELETE CASCADE
);

-- Elections Table: Stores details of elections
CREATE TABLE Elections (
    electionID INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,  
    startDate DATE NOT NULL,  
    description varchar(255) DEFAULT NULL,
    endDate DATE NOT NULL,  
    createdDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Scheduled', 'Ongoing', 'Completed') DEFAULT 'Scheduled'
);

-- Votes Table: Stores votes cast by students
CREATE TABLE Votes (
    voteID INT PRIMARY KEY AUTO_INCREMENT,
    electionID INT,  
    candidateID INT,  
    studentID INT,  
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (electionID) REFERENCES Elections(electionID) ON DELETE CASCADE,
    FOREIGN KEY (candidateID) REFERENCES Candidates(candidateID) ON DELETE CASCADE,
    FOREIGN KEY (studentID) REFERENCES Students(studentID) ON DELETE CASCADE,
    UNIQUE (electionID, studentID)  
);

-- Results Table: Stores election results
CREATE TABLE Results (
    resultID INT PRIMARY KEY AUTO_INCREMENT,
    electionID INT,  
    candidateID INT, 
    voteCount INT DEFAULT 0,  
    percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(20) DEFAULT 'Preliminary',
    lastUpdated DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (electionID) REFERENCES Elections(electionID) ON DELETE CASCADE,
    FOREIGN KEY (candidateID) REFERENCES Candidates(candidateID) ON DELETE CASCADE
);

-- Categories Table: Stores election categories
CREATE TABLE Categories (
    categoryID INT PRIMARY KEY AUTO_INCREMENT,
    electionID INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    addedBy INT DEFAULT NULL,
    updatedBy INT DEFAULT NULL,
    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (electionID) REFERENCES Elections(electionID) ON DELETE CASCADE,
    FOREIGN KEY (addedBy) REFERENCES Students(studentID) ON DELETE SET NULL,
    FOREIGN KEY (updatedBy) REFERENCES Students(studentID) ON DELETE SET NULL
);


--- Students
INSERT INTO `students` (`studentID`, `studentNumber`, `name`, `dateOfBirth`, `department`, `contactNumber`, `email`, `registrationDate`, `status`) VALUES ('10958252', '4593956', 'Aristocrat Junior', '2002-04-18', 'Computer Science', '0551784926', 'ayimobuobi@gmail.com', '2025-03-18', 'Active');
INSERT INTO `students` (`studentID`, `studentNumber`, `name`, `dateOfBirth`, `department`, `contactNumber`, `email`, `registrationDate`, `status`) VALUES ('10948232', '4572556', 'Archimedes Great', '2003-02-02', 'Information Technology', '0501888952', 'archimedes@aol.com', '2025-03-18', 'Active');
INSERT INTO `students` (`studentID`, `studentNumber`, `name`, `dateOfBirth`, `department`, `contactNumber`, `email`, `registrationDate`, `status`) VALUES ('10928212', '4572356', 'Aristotle Columbus', '2001-09-23', 'Economics', '0251584723', 'aristotle@yahoo.com', '2025-03-18', 'Active');

--- Candidates
INSERT INTO `candidates` (`candidateID`, `studentID`, `position`, `manifesto`, `status`) VALUES (NULL, '10928212', 'SRC-PRESIDENT', 'I want to be a president', 'Pending');
INSERT INTO `candidates` (`candidateID`, `studentID`, `position`, `manifesto`, `status`) VALUES (NULL, '10948220', 'TRESURER', 'I want to be a tresurer', 'Pending');
INSERT INTO `candidates` (`candidateID`, `studentID`, `position`, `manifesto`, `status`) VALUES (NULL, '10928212', 'SECRETARY', 'I want to be a secretary', 'Pending');

--- Election
INSERT INTO `elections` (`electionID`, `name`, `startDate`, `endDate`, `status`) VALUES ('2832234', 'David Ayim Obuobi', '2025-03-18', '2025-03-20', 'Scheduled');
INSERT INTO `elections` (`electionID`, `name`, `startDate`, `endDate`, `status`) VALUES ('2832235', 'Nana Addo Dankwa', '2025-03-19', '2025-03-22', 'Scheduled');
INSERT INTO `elections` (`electionID`, `name`, `startDate`, `endDate`, `status`) VALUES ('2832234', 'John Dramani Mahama ', '2025-03-20', '2025-03-23', 'Scheduled');

--- Votes
INSERT INTO `votes` (`voteID`, `electionID`, `candidateID`, `studentID`, `timestamp`) VALUES ('739321', '2832234', '2372714', '10928212', '2025-03-18 21:11:03');
INSERT INTO `votes` (`voteID`, `electionID`, `candidateID`, `studentID`, `timestamp`) VALUES ('731421', '2789234', '2393814', '10639212', '2025-03-18 21:10:08');
INSERT INTO `votes` (`voteID`, `electionID`, `candidateID`, `studentID`, `timestamp`) VALUES ('739221', '2802234', '2362314', '10720212', '2025-03-18 21:03:09');


--- Results
INSERT INTO `results` (`resultID`, `electionID`, `candidateID`, `voteCount`, `percentage`) VALUES ('44557', '2832234', '237713', '52', '42.7');
INSERT INTO `results` (`resultID`, `electionID`, `candidateID`, `voteCount`, `percentage`) VALUES ('47857', '2809234', '237013', '80', '92.7');
INSERT INTO `results` (`resultID`, `electionID`, `candidateID`, `voteCount`, `percentage`) VALUES ('40857', '2879834', '2309813', '32', '20.7');


--- List all registered students
SELECT * FROM Students;

--- List candidates for a specific election
SELECT c.candidateID, s.name AS CandidateName, c.position, c.manifesto 
FROM Candidates c
JOIN Students s ON c.studentID = s.studentID
WHERE c.status = 'Approved';

--- Show election results for completed elections
SELECT e.name AS ElectionName, s.name AS CandidateName, r.voteCount, r.percentage 
FROM Results r
JOIN Candidates c ON r.candidateID = c.candidateID
JOIN Students s ON c.studentID = s.studentID
JOIN Elections e ON r.electionID = e.electionID
WHERE e.status = 'Completed'
ORDER BY r.voteCount DESC;

--- Show upcoming elections
SELECT * FROM Elections WHERE status = 'Scheduled';

--- Show all votes cast in an election (latest votes first)
SELECT v.voteID, e.name AS ElectionName, s.name AS VoterName, c.position, c.manifesto, v.timestamp
FROM Votes v
JOIN Elections e ON v.electionID = e.electionID
JOIN Students s ON v.studentID = s.studentID
JOIN Candidates c ON v.candidateID = c.candidateID
ORDER BY v.timestamp DESC;

--- Count total votes for each candidate in an election
SELECT e.name AS ElectionName, c.position, s.name AS CandidateName, COUNT(v.voteID) AS TotalVotes
FROM Votes v
JOIN Candidates c ON v.candidateID = c.candidateID
JOIN Students s ON c.studentID = s.studentID
JOIN Elections e ON v.electionID = e.electionID
GROUP BY e.name, c.position, s.name
ORDER BY TotalVotes DESC;
