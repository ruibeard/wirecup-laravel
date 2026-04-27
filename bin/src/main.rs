use std::io::{self, Read};

fn main() {
    let mut input = String::new();
    io::stdin().read_to_string(&mut input).unwrap();
    let bpe = tiktoken_rs::cl100k_base().unwrap();
    let tokens = bpe.encode_with_special_tokens(&input);
    println!("{}", tokens.len());
}
