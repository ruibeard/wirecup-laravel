#!/usr/bin/env bash
# Build tokencount binaries for all supported platforms.
# Requires Rust with cross-compilation targets installed.

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BIN_DIR="$SCRIPT_DIR/../bin"
mkdir -p "$BIN_DIR"

# macOS ARM64 (Apple Silicon)
echo "Building for macOS ARM64..."
cargo build --manifest-path "$SCRIPT_DIR/Cargo.toml" --release --target aarch64-apple-darwin 2>/dev/null || echo "  Skipped (target not installed)"
if [ -f "$SCRIPT_DIR/target/aarch64-apple-darwin/release/tokencount" ]; then
    cp "$SCRIPT_DIR/target/aarch64-apple-darwin/release/tokencount" "$BIN_DIR/tokencount-darwin-arm64"
    echo "  -> bin/tokencount-darwin-arm64"
fi

# macOS x64
echo "Building for macOS x64..."
cargo build --manifest-path "$SCRIPT_DIR/Cargo.toml" --release --target x86_64-apple-darwin 2>/dev/null || echo "  Skipped (target not installed)"
if [ -f "$SCRIPT_DIR/target/x86_64-apple-darwin/release/tokencount" ]; then
    cp "$SCRIPT_DIR/target/x86_64-apple-darwin/release/tokencount" "$BIN_DIR/tokencount-darwin-x64"
    echo "  -> bin/tokencount-darwin-x64"
fi

# Linux x64
echo "Building for Linux x64..."
cargo build --manifest-path "$SCRIPT_DIR/Cargo.toml" --release --target x86_64-unknown-linux-gnu 2>/dev/null || echo "  Skipped (target not installed)"
if [ -f "$SCRIPT_DIR/target/x86_64-unknown-linux-gnu/release/tokencount" ]; then
    cp "$SCRIPT_DIR/target/x86_64-unknown-linux-gnu/release/tokencount" "$BIN_DIR/tokencount-linux-x64"
    echo "  -> bin/tokencount-linux-x64"
fi

# Linux ARM64
echo "Building for Linux ARM64..."
cargo build --manifest-path "$SCRIPT_DIR/Cargo.toml" --release --target aarch64-unknown-linux-gnu 2>/dev/null || echo "  Skipped (target not installed)"
if [ -f "$SCRIPT_DIR/target/aarch64-unknown-linux-gnu/release/tokencount" ]; then
    cp "$SCRIPT_DIR/target/aarch64-unknown-linux-gnu/release/tokencount" "$BIN_DIR/tokencount-linux-arm64"
    echo "  -> bin/tokencount-linux-arm64"
fi

echo "Done."
