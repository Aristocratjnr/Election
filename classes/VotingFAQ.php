<?php
/**
 * AI Chat FAQ Knowledge Base
 * Contains voting-related frequently asked questions and responses
 */

class VotingFAQKnowledgeBase {
    
    public static function getVotingFAQs() {
        return [
            'voting_process' => [
                'question' => 'How do I vote in the election?',
                'answer' => 'To vote: 1) Review all candidates and their manifestos by clicking "View Manifesto" on candidate cards, 2) Select one candidate per position by clicking on their card, 3) Click the "Submit Your Vote" button at the bottom, 4) Review your selections in the confirmation popup, 5) Click "Confirm and Submit" to finalize your vote. Remember, you can only vote once and cannot change your vote after submission!',
                'keywords' => ['vote', 'voting', 'how to vote', 'process', 'steps', 'cast ballot']
            ],
            
            'voting_requirements' => [
                'question' => 'Who can vote and what are the requirements?',
                'answer' => 'All registered students can participate in the election. Requirements: 1) You must be logged in with your student credentials, 2) The election must be currently active/ongoing, 3) You must be enrolled as a student in the system, 4) Each student gets exactly one vote per position. Guest users or non-students cannot vote.',
                'keywords' => ['requirements', 'who can vote', 'eligible', 'qualifications', 'criteria', 'allowed']
            ],
            
            'election_timing' => [
                'question' => 'When does the election start and end?',
                'answer' => 'You can see the exact election schedule on this page with the countdown timer. The timer shows days, hours, minutes, and seconds remaining. The election dates are displayed above the timer. Make sure to vote before the deadline - no votes will be accepted after the election ends!',
                'keywords' => ['when', 'time', 'schedule', 'deadline', 'start', 'end', 'date', 'timer', 'countdown']
            ],
            
            'candidate_info' => [
                'question' => 'How can I learn about the candidates?',
                'answer' => 'Each candidate card shows: 1) Candidate photo, 2) Name and department, 3) Position they\'re running for, 4) "View Manifesto" button to read their policies and plans. Take time to read all manifestos before voting. The manifesto explains each candidate\'s vision and what they plan to do if elected.',
                'keywords' => ['candidates', 'manifesto', 'platform', 'policies', 'who is running', 'information', 'learn about']
            ],
            
            'vote_security' => [
                'question' => 'Is my vote secure and private?',
                'answer' => 'Yes! Your vote is completely secure and anonymous. We use advanced blockchain technology to ensure vote integrity and prevent tampering. Your identity is separated from your vote choices - no one can see how you voted, not even administrators. The system only records that you voted, not your specific choices.',
                'keywords' => ['secure', 'security', 'anonymous', 'privacy', 'private', 'confidential', 'safe', 'blockchain']
            ],
            
            'change_vote' => [
                'question' => 'Can I change my vote after submitting?',
                'answer' => 'No, once you click "Confirm and Submit" your vote is final and cannot be changed, modified, or cancelled. This ensures election integrity and prevents vote tampering. Please review all your selections carefully in the confirmation popup before final submission.',
                'keywords' => ['change vote', 'modify', 'edit', 'undo', 'cancel', 'revote', 'final']
            ],
            
            'technical_issues' => [
                'question' => 'What should I do if I have voting problems?',
                'answer' => 'If you experience technical issues: 1) Refresh the page and try again, 2) Clear your browser cache and cookies, 3) Try using a different browser (Chrome, Firefox, Safari, Edge), 4) Ensure you have a stable internet connection, 5) Disable browser extensions temporarily, 6) Try on a different device. If problems persist, contact the election administrator immediately.',
                'keywords' => ['problems', 'issues', 'errors', 'not working', 'technical', 'help', 'support', 'bug', 'glitch']
            ],
            
            'election_results' => [
                'question' => 'When and how will I see the results?',
                'answer' => 'Live preliminary results are shown on this page during voting, but these are not final. Official final results will be announced after the election closes and all votes are verified. You can view detailed results on the Results page once the election ends. Winners will be officially announced by the election committee.',
                'keywords' => ['results', 'winner', 'outcome', 'count', 'tally', 'who won', 'final results']
            ],
            
            'voting_positions' => [
                'question' => 'What positions am I voting for?',
                'answer' => 'You are voting for student leadership positions as shown on this page. Each position is displayed separately with its candidates. Common positions include President, Vice President, Secretary, Treasurer, and other student government roles. You must select exactly one candidate for each available position.',
                'keywords' => ['positions', 'roles', 'offices', 'what am i voting for', 'leadership', 'student government']
            ],
            
            'browser_compatibility' => [
                'question' => 'What browsers and devices can I use?',
                'answer' => 'For the best voting experience use: Modern browsers (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+), Enable JavaScript and cookies, Stable internet connection. The system works on desktop, laptop, tablet, and mobile devices. Avoid very old browsers or Internet Explorer.',
                'keywords' => ['browser', 'device', 'mobile', 'computer', 'compatibility', 'requirements', 'phone', 'tablet']
            ],
            
            'vote_counting' => [
                'question' => 'How are votes counted and verified?',
                'answer' => 'Votes are automatically counted using secure blockchain technology. Each vote is encrypted and verified for authenticity. The system prevents duplicate voting and ensures accurate tallying. Vote counts are updated in real-time and cross-verified for accuracy before final results are announced.',
                'keywords' => ['counting', 'verification', 'accuracy', 'blockchain', 'duplicate', 'tally', 'secure counting']
            ],
            
            'multiple_positions' => [
                'question' => 'Do I need to vote for all positions?',
                'answer' => 'Yes, you must select a candidate for each available position to submit your vote. If you skip any position, the system will prompt you to complete all selections before allowing submission. This ensures fair representation across all student leadership roles.',
                'keywords' => ['all positions', 'skip position', 'multiple', 'complete', 'required', 'must vote']
            ]
        ];
    }
    
    public static function searchFAQ($query) {
        $faqs = self::getVotingFAQs();
        $query = strtolower(trim($query));
        $matches = [];
        
        foreach ($faqs as $id => $faq) {
            $score = 0;
            
            // Check keywords
            foreach ($faq['keywords'] as $keyword) {
                if (strpos($query, strtolower($keyword)) !== false) {
                    $score += 1;
                }
            }
            
            // Check question text
            if (strpos(strtolower($faq['question']), $query) !== false) {
                $score += 2;
            }
            
            if ($score > 0) {
                $matches[] = [
                    'id' => $id,
                    'score' => $score,
                    'question' => $faq['question'],
                    'answer' => $faq['answer']
                ];
            }
        }
        
        // Sort by score (highest first)
        usort($matches, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return $matches;
    }
    
    public static function isVotingRelated($message) {
        $votingKeywords = [
            'vote', 'voting', 'election', 'candidate', 'ballot', 'cast', 'poll', 'polls',
            'manifesto', 'platform', 'campaign', 'result', 'results', 'winner', 'tally',
            'count', 'deadline', 'schedule', 'when', 'time', 'date', 'end', 'start',
            'secure', 'anonymous', 'privacy', 'safe', 'confidential', 'blockchain',
            'position', 'office', 'role', 'leadership', 'student government',
            'submit', 'confirm', 'change', 'modify', 'undo', 'cancel',
            'problem', 'issue', 'error', 'help', 'support', 'technical',
            'browser', 'device', 'mobile', 'computer', 'internet'
        ];
        
        $message = strtolower($message);
        foreach ($votingKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
?>
